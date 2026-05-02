<?php

namespace App\Services\SocialMedia\Platforms;

use App\Models\SocialAccount;
use App\Models\SocialPost;
use App\Services\SocialMedia\Contracts\SocialPlatformInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class InstagramService implements SocialPlatformInterface
{
    private string $apiVersion = 'v19.0';
    private string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = "https://graph.facebook.com/{$this->apiVersion}";
    }

    public function getAuthUrl(): string
    {
        $params = http_build_query([
            'client_id'     => config('services.facebook_social.client_id'),
            'redirect_uri'  => route('admin.social.instagram.callback'),
            'scope'         => 'instagram_basic,instagram_content_publish,pages_read_engagement,pages_show_list,public_profile',
            'response_type' => 'code',
            'state'         => csrf_token(),
        ]);

        return "https://www.facebook.com/dialog/oauth?{$params}";
    }

    public function handleCallback(string $code): array
    {
        // نفس Facebook OAuth - يستخدم Meta Graph API
        $response = Http::get("https://graph.facebook.com/oauth/access_token", [
            'client_id'     => config('services.facebook_social.client_id'),
            'client_secret' => config('services.facebook_social.client_secret'),
            'redirect_uri'  => route('admin.social.instagram.callback'),
            'code'          => $code,
        ]);

        if (!$response->successful()) {
            throw new \Exception('فشل الحصول على Access Token من Instagram');
        }

        $accessToken = $response->json()['access_token'];

        // الحصول على الصفحات المرتبطة بحسابات Instagram
        $pagesResponse = Http::get("{$this->baseUrl}/me/accounts", [
            'access_token' => $accessToken,
        ]);

        $pages = $pagesResponse->json()['data'] ?? [];
        $igAccounts = [];

        foreach ($pages as $page) {
            $igResponse = Http::get("{$this->baseUrl}/{$page['id']}", [
                'fields'       => 'instagram_business_account',
                'access_token' => $page['access_token'],
            ]);

            if ($igResponse->successful() && isset($igResponse->json()['instagram_business_account'])) {
                $igId = $igResponse->json()['instagram_business_account']['id'];

                $igInfoResponse = Http::get("{$this->baseUrl}/{$igId}", [
                    'fields'       => 'username,profile_picture_url',
                    'access_token' => $page['access_token'],
                ]);

                $igInfo = $igInfoResponse->json();
                $igAccounts[] = [
                    'ig_id'          => $igId,
                    'page_id'        => $page['id'],
                    'page_token'     => $page['access_token'],
                    'username'       => $igInfo['username'] ?? $page['name'],
                    'profile_picture'=> $igInfo['profile_picture_url'] ?? null,
                ];
            }
        }

        return [
            'access_token' => $accessToken,
            'ig_accounts'  => $igAccounts,
        ];
    }

    public function publish(SocialAccount $account, SocialPost $post): array
    {
        try {
            $igAccountId = $account->account_id;
            $pageToken   = $account->access_token;

            $result = match($post->media_type) {
                'image' => $this->publishImage($igAccountId, $pageToken, $post),
                'video', 'reel' => $this->publishVideo($igAccountId, $pageToken, $post),
                'story' => $this->publishStory($igAccountId, $pageToken, $post),
                default => throw new \Exception('Instagram لا يدعم النشر النصي البحت، يجب إضافة صورة أو فيديو'),
            };

            return ['success' => true, 'platform_post_id' => $result];

        } catch (\Exception $e) {
            Log::error('Instagram publish error: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    private function publishImage(string $igId, string $token, SocialPost $post): string
    {
        $mediaPaths = $post->media_paths ?? [];

        if (empty($mediaPaths)) {
            throw new \Exception('Instagram يتطلب صورة أو فيديو للنشر');
        }

        if (count($mediaPaths) === 1) {
            // Single image
            $containerResponse = Http::post("{$this->baseUrl}/{$igId}/media", [
                'image_url'    => url($mediaPaths[0]),
                'caption'      => $post->content,
                'access_token' => $token,
            ]);

            if (!$containerResponse->successful()) {
                throw new \Exception($containerResponse->json()['error']['message'] ?? 'فشل إنشاء الحاوية');
            }

            $containerId = $containerResponse->json()['id'];
        } else {
            // Carousel (صور متعددة)
            $childIds = [];
            foreach ($mediaPaths as $path) {
                $childResponse = Http::post("{$this->baseUrl}/{$igId}/media", [
                    'image_url'       => url($path),
                    'is_carousel_item'=> true,
                    'access_token'    => $token,
                ]);
                if ($childResponse->successful()) {
                    $childIds[] = $childResponse->json()['id'];
                }
            }

            $containerResponse = Http::post("{$this->baseUrl}/{$igId}/media", [
                'media_type'   => 'CAROUSEL',
                'children'     => implode(',', $childIds),
                'caption'      => $post->content,
                'access_token' => $token,
            ]);

            if (!$containerResponse->successful()) {
                throw new \Exception($containerResponse->json()['error']['message'] ?? 'فشل إنشاء الكاروسيل');
            }

            $containerId = $containerResponse->json()['id'];
        }

        // نشر الحاوية
        return $this->publishContainer($igId, $token, $containerId);
    }

    private function publishVideo(string $igId, string $token, SocialPost $post): string
    {
        $mediaPaths = $post->media_paths ?? [];
        if (empty($mediaPaths)) {
            throw new \Exception('يجب تحديد فيديو للنشر');
        }

        $containerResponse = Http::post("{$this->baseUrl}/{$igId}/media", [
            'media_type'   => $post->media_type === 'reel' ? 'REELS' : 'VIDEO',
            'video_url'    => url($mediaPaths[0]),
            'caption'      => $post->content,
            'access_token' => $token,
        ]);

        if (!$containerResponse->successful()) {
            throw new \Exception($containerResponse->json()['error']['message'] ?? 'فشل رفع الفيديو');
        }

        // انتظار معالجة الفيديو
        $containerId = $containerResponse->json()['id'];
        $this->waitForVideoProcessing($igId, $token, $containerId);

        return $this->publishContainer($igId, $token, $containerId);
    }

    private function publishStory(string $igId, string $token, SocialPost $post): string
    {
        $mediaPaths = $post->media_paths ?? [];
        if (empty($mediaPaths)) {
            throw new \Exception('يجب تحديد صورة أو فيديو للستوري');
        }

        $containerResponse = Http::post("{$this->baseUrl}/{$igId}/media", [
            'image_url'    => url($mediaPaths[0]),
            'media_type'   => 'STORIES',
            'access_token' => $token,
        ]);

        if (!$containerResponse->successful()) {
            throw new \Exception($containerResponse->json()['error']['message'] ?? 'فشل إنشاء الستوري');
        }

        return $this->publishContainer($igId, $token, $containerResponse->json()['id']);
    }

    private function publishContainer(string $igId, string $token, string $containerId): string
    {
        $response = Http::post("{$this->baseUrl}/{$igId}/media_publish", [
            'creation_id'  => $containerId,
            'access_token' => $token,
        ]);

        if (!$response->successful() || !isset($response->json()['id'])) {
            throw new \Exception($response->json()['error']['message'] ?? 'فشل نشر المحتوى');
        }

        return $response->json()['id'];
    }

    private function waitForVideoProcessing(string $igId, string $token, string $containerId, int $maxRetries = 10): void
    {
        for ($i = 0; $i < $maxRetries; $i++) {
            sleep(5);
            $status = Http::get("{$this->baseUrl}/{$containerId}", [
                'fields'       => 'status_code',
                'access_token' => $token,
            ]);

            if ($status->json()['status_code'] === 'FINISHED') {
                return;
            }

            if ($status->json()['status_code'] === 'ERROR') {
                throw new \Exception('فشلت معالجة الفيديو على Instagram');
            }
        }
    }

    public function uploadImage(SocialAccount $account, string $imagePath): ?string { return null; }
    public function uploadVideo(SocialAccount $account, string $videoPath): ?string { return null; }
    public function refreshToken(SocialAccount $account): bool { return false; }

    public function getPostEngagement(SocialAccount $account, string $platformPostId): array
    {
        $response = Http::get("{$this->baseUrl}/{$platformPostId}", [
            'fields'       => 'like_count,comments_count',
            'access_token' => $account->access_token,
        ]);

        if (!$response->successful()) return [];

        $data = $response->json();
        return [
            'likes'    => $data['like_count'] ?? 0,
            'comments' => $data['comments_count'] ?? 0,
        ];
    }
}
