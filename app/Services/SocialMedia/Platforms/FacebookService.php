<?php

namespace App\Services\SocialMedia\Platforms;

use App\Models\SocialAccount;
use App\Models\SocialPost;
use App\Services\SocialMedia\Contracts\SocialPlatformInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FacebookService implements SocialPlatformInterface
{
    private string $apiVersion = 'v20.0';
    private string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = "https://graph.facebook.com/{$this->apiVersion}";
    }

    public function getAuthUrl(): string
    {
        $params = http_build_query([
            'client_id'     => config('services.facebook_social.client_id'),
            'redirect_uri'  => config('services.facebook_social.redirect'),
            'scope'         => 'public_profile,email,pages_show_list,pages_read_engagement,pages_manage_posts',
            'response_type' => 'code',
            'state'         => csrf_token(),
        ]);

        return "https://www.facebook.com/dialog/oauth?{$params}";
    }

    public function handleCallback(string $code): array
    {
        // تبادل الـ code بـ access token
        $response = Http::get("https://graph.facebook.com/oauth/access_token", [
            'client_id'     => config('services.facebook_social.client_id'),
            'client_secret' => config('services.facebook_social.client_secret'),
            'redirect_uri'  => config('services.facebook_social.redirect'),
            'code'          => $code,
        ]);

        if (!$response->successful()) {
            throw new \Exception('فشل الحصول على Access Token من Facebook: ' . $response->body());
        }

        $tokenData = $response->json();
        $accessToken = $tokenData['access_token'];

        // الحصول على Long-Lived Token
        $longLivedResponse = Http::get("https://graph.facebook.com/oauth/access_token", [
            'grant_type'        => 'fb_exchange_token',
            'client_id'         => config('services.facebook_social.client_id'),
            'client_secret'     => config('services.facebook_social.client_secret'),
            'fb_exchange_token' => $accessToken,
        ]);

        if ($longLivedResponse->successful()) {
            $accessToken = $longLivedResponse->json()['access_token'];
        }

        // الحصول على بيانات المستخدم والصفحات في طلب واحد (طريقة بديلة)
        $userResponse = Http::get("{$this->baseUrl}/me", [
            'fields'       => 'id,name,picture,accounts{name,access_token,id,tasks,picture}',
            'access_token' => $accessToken,
        ]);

        $userData = $userResponse->json();
        $user = [
            'id'      => $userData['id'] ?? null,
            'name'    => $userData['name'] ?? null,
            'picture' => $userData['picture'] ?? null,
        ];

        $pages = $userData['accounts']['data'] ?? [];

        Log::info('Facebook Login Attempt:', [
            'user' => $user['name'] ?? 'Unknown',
            'token_exists' => !empty($accessToken),
            'pages_count' => count($pages),
            'full_response' => $userData
        ]);

        return [
            'access_token' => $accessToken,
            'user_id'      => $user['id'] ?? null,
            'user_name'    => $user['name'] ?? null,
            'avatar'       => $user['picture']['data']['url'] ?? null,
            'pages'        => $pages,
        ];
    }

    public function publish(SocialAccount $account, SocialPost $post): array
    {
        try {
            $pageId      = $account->page_id ?? $account->account_id;
            $accessToken = $account->access_token;

            // الحصول على Page Access Token
            $pageTokenResponse = Http::get("{$this->baseUrl}/{$pageId}", [
                'fields'       => 'access_token',
                'access_token' => $accessToken,
            ]);

            if ($pageTokenResponse->successful() && isset($pageTokenResponse->json()['access_token'])) {
                $accessToken = $pageTokenResponse->json()['access_token'];
            }

            // نشر حسب نوع المحتوى
            $result = match($post->media_type) {
                'image' => $this->publishImage($pageId, $accessToken, $post),
                'video' => $this->publishVideo($pageId, $accessToken, $post),
                default => $this->publishText($pageId, $accessToken, $post),
            };

            return ['success' => true, 'platform_post_id' => $result];

        } catch (\Exception $e) {
            Log::error('Facebook publish error: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    private function publishText(string $pageId, string $token, SocialPost $post): string
    {
        $response = Http::post("{$this->baseUrl}/{$pageId}/feed", [
            'message'      => $post->content,
            'access_token' => $token,
        ]);

        if (!$response->successful() || !isset($response->json()['id'])) {
            throw new \Exception($response->json()['error']['message'] ?? 'Unknown error');
        }

        return $response->json()['id'];
    }

    private function publishImage(string $pageId, string $token, SocialPost $post): string
    {
        $mediaPaths = $post->media_paths ?? [];

        if (empty($mediaPaths)) {
            return $this->publishText($pageId, $token, $post);
        }

        // نشر صورة واحدة
        if (count($mediaPaths) === 1) {
            $response = Http::post("{$this->baseUrl}/{$pageId}/photos", [
                'url'          => url($mediaPaths[0]),
                'caption'      => $post->content,
                'access_token' => $token,
            ]);

            if (!$response->successful() || !isset($response->json()['post_id'])) {
                throw new \Exception($response->json()['error']['message'] ?? 'Unknown error');
            }

            return $response->json()['post_id'];
        }

        // نشر صور متعددة
        $photoIds = [];
        foreach ($mediaPaths as $path) {
            $photoResponse = Http::post("{$this->baseUrl}/{$pageId}/photos", [
                'url'          => url($path),
                'published'    => false,
                'access_token' => $token,
            ]);
            if ($photoResponse->successful()) {
                $photoIds[] = ['media_fbid' => $photoResponse->json()['id']];
            }
        }

        $response = Http::post("{$this->baseUrl}/{$pageId}/feed", [
            'message'          => $post->content,
            'attached_media'   => json_encode($photoIds),
            'access_token'     => $token,
        ]);

        if (!$response->successful() || !isset($response->json()['id'])) {
            throw new \Exception($response->json()['error']['message'] ?? 'Unknown error');
        }

        return $response->json()['id'];
    }

    private function publishVideo(string $pageId, string $token, SocialPost $post): string
    {
        $mediaPaths = $post->media_paths ?? [];
        if (empty($mediaPaths)) {
            return $this->publishText($pageId, $token, $post);
        }

        $videoPath = public_path($mediaPaths[0]);
        $response  = Http::attach('source', fopen($videoPath, 'r'), basename($videoPath))
            ->post("https://graph-video.facebook.com/{$this->apiVersion}/{$pageId}/videos", [
                'description'  => $post->content,
                'access_token' => $token,
            ]);

        if (!$response->successful() || !isset($response->json()['id'])) {
            throw new \Exception($response->json()['error']['message'] ?? 'Unknown error');
        }

        return $response->json()['id'];
    }

    public function uploadImage(SocialAccount $account, string $imagePath): ?string
    {
        return null; // يتم داخل publish
    }

    public function uploadVideo(SocialAccount $account, string $videoPath): ?string
    {
        return null; // يتم داخل publish
    }

    public function refreshToken(SocialAccount $account): bool
    {
        // Facebook Long-Lived tokens تدوم 60 يوم ولا تحتاج refresh تلقائي
        return false;
    }

    public function getPostEngagement(SocialAccount $account, string $platformPostId): array
    {
        $response = Http::get("{$this->baseUrl}/{$platformPostId}", [
            'fields'       => 'likes.summary(true),comments.summary(true),shares',
            'access_token' => $account->access_token,
        ]);

        if (!$response->successful()) {
            return [];
        }

        $data = $response->json();
        return [
            'likes'    => $data['likes']['summary']['total_count'] ?? 0,
            'comments' => $data['comments']['summary']['total_count'] ?? 0,
            'shares'   => $data['shares']['count'] ?? 0,
        ];
    }
}
