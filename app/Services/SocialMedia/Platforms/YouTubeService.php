<?php

namespace App\Services\SocialMedia\Platforms;

use App\Models\SocialAccount;
use App\Models\SocialPost;
use App\Services\SocialMedia\Contracts\SocialPlatformInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class YouTubeService implements SocialPlatformInterface
{
    private string $baseUrl = 'https://www.googleapis.com/youtube/v3';

    public function getAuthUrl(): string
    {
        $params = http_build_query([
            'client_id'     => config('services.youtube.client_id'),
            'redirect_uri'  => config('services.youtube.redirect'),
            'response_type' => 'code',
            'scope'         => 'https://www.googleapis.com/auth/youtube.upload https://www.googleapis.com/auth/youtube',
            'access_type'   => 'offline',
            'prompt'        => 'consent',
            'state'         => csrf_token(),
        ]);
        return "https://accounts.google.com/o/oauth2/v2/auth?{$params}";
    }

    public function handleCallback(string $code): array
    {
        $response = Http::post('https://oauth2.googleapis.com/token', [
            'code'          => $code,
            'client_id'     => config('services.youtube.client_id'),
            'client_secret' => config('services.youtube.client_secret'),
            'redirect_uri'  => config('services.youtube.redirect'),
            'grant_type'    => 'authorization_code',
        ]);

        if (!$response->successful()) {
            throw new \Exception('فشل الحصول على Access Token من YouTube');
        }

        $data = $response->json();

        $channelResponse = Http::withToken($data['access_token'])
            ->get("{$this->baseUrl}/channels", [
                'part' => 'snippet',
                'mine' => 'true',
            ]);

        $channel = $channelResponse->json()['items'][0] ?? null;

        return [
            'access_token'  => $data['access_token'],
            'refresh_token' => $data['refresh_token'] ?? null,
            'channel_id'    => $channel['id'] ?? null,
            'channel_name'  => $channel['snippet']['title'] ?? 'YouTube Channel',
            'avatar'        => $channel['snippet']['thumbnails']['default']['url'] ?? null,
            'expires_in'    => $data['expires_in'] ?? 3600,
        ];
    }

    public function publish(SocialAccount $account, SocialPost $post): array
    {
        try {
            if ($post->media_type !== 'video') {
                throw new \Exception('YouTube يدعم الفيديو فقط');
            }

            $mediaPaths = $post->media_paths ?? [];
            if (empty($mediaPaths)) {
                throw new \Exception('يجب تحديد فيديو للنشر على YouTube');
            }

            $videoPath = public_path($mediaPaths[0]);

            // إنشاء metadata للفيديو
            $metadata = [
                'snippet' => [
                    'title'       => $post->title ?? substr($post->content, 0, 100),
                    'description' => $post->content,
                    'tags'        => [],
                    'categoryId'  => '22', // People & Blogs
                ],
                'status' => [
                    'privacyStatus' => 'public',
                ],
            ];

            // رفع الفيديو
            $uploadResponse = Http::withToken($account->access_token)
                ->withHeaders(['X-Upload-Content-Type' => 'video/*'])
                ->attach('file', fopen($videoPath, 'r'), basename($videoPath))
                ->post('https://www.googleapis.com/upload/youtube/v3/videos?uploadType=multipart&part=snippet,status', $metadata);

            if (!$uploadResponse->successful() || !isset($uploadResponse->json()['id'])) {
                throw new \Exception($uploadResponse->json()['error']['message'] ?? 'فشل رفع الفيديو');
            }

            return ['success' => true, 'platform_post_id' => $uploadResponse->json()['id']];
        } catch (\Exception $e) {
            Log::error('YouTube publish error: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function refreshToken(SocialAccount $account): bool
    {
        if (!$account->refresh_token) return false;

        $response = Http::post('https://oauth2.googleapis.com/token', [
            'client_id'     => config('services.youtube.client_id'),
            'client_secret' => config('services.youtube.client_secret'),
            'refresh_token' => $account->refresh_token,
            'grant_type'    => 'refresh_token',
        ]);

        if ($response->successful()) {
            $account->update([
                'access_token'    => $response->json()['access_token'],
                'token_expires_at'=> now()->addSeconds($response->json()['expires_in'] ?? 3600),
            ]);
            return true;
        }
        return false;
    }

    public function uploadImage(SocialAccount $account, string $imagePath): ?string { return null; }
    public function uploadVideo(SocialAccount $account, string $videoPath): ?string { return null; }

    public function getPostEngagement(SocialAccount $account, string $platformPostId): array
    {
        $response = Http::withToken($account->access_token)
            ->get("{$this->baseUrl}/videos", [
                'part' => 'statistics',
                'id'   => $platformPostId,
            ]);

        if (!$response->successful()) return [];

        $stats = $response->json()['items'][0]['statistics'] ?? [];
        return [
            'views'    => $stats['viewCount'] ?? 0,
            'likes'    => $stats['likeCount'] ?? 0,
            'comments' => $stats['commentCount'] ?? 0,
        ];
    }
}
