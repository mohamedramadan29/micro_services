<?php

namespace App\Services\SocialMedia\Platforms;

use App\Models\SocialAccount;
use App\Models\SocialPost;
use App\Services\SocialMedia\Contracts\SocialPlatformInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TikTokService implements SocialPlatformInterface
{
    private string $baseUrl = 'https://open.tiktokapis.com/v2';

    public function getAuthUrl(): string
    {
        $params = http_build_query([
            'client_key'    => config('services.tiktok.client_key'),
            'redirect_uri'  => config('services.tiktok.redirect'),
            'response_type' => 'code',
            'scope'         => 'user.info.basic,video.publish,video.upload',
            'state'         => csrf_token(),
        ]);
        return "https://www.tiktok.com/v2/auth/authorize?{$params}";
    }

    public function handleCallback(string $code): array
    {
        $response = Http::post('https://open.tiktokapis.com/v2/oauth/token/', [
            'client_key'    => config('services.tiktok.client_key'),
            'client_secret' => config('services.tiktok.client_secret'),
            'code'          => $code,
            'grant_type'    => 'authorization_code',
            'redirect_uri'  => config('services.tiktok.redirect'),
        ]);

        if (!$response->successful()) {
            throw new \Exception('فشل الحصول على Access Token من TikTok');
        }

        $data = $response->json()['data'] ?? $response->json();
        $userResponse = Http::withToken($data['access_token'])
            ->post("{$this->baseUrl}/user/info/", ['fields' => 'open_id,avatar_url,display_name']);
        $user = $userResponse->json()['data']['user'] ?? [];

        return [
            'access_token'  => $data['access_token'],
            'refresh_token' => $data['refresh_token'] ?? null,
            'open_id'       => $data['open_id'] ?? $user['open_id'] ?? null,
            'user_name'     => $user['display_name'] ?? 'TikTok User',
            'avatar'        => $user['avatar_url'] ?? null,
            'expires_in'    => $data['expires_in'] ?? 86400,
        ];
    }

    public function publish(SocialAccount $account, SocialPost $post): array
    {
        try {
            if ($post->media_type !== 'video') {
                throw new \Exception('TikTok يدعم الفيديو فقط');
            }
            $mediaPaths = $post->media_paths ?? [];
            if (empty($mediaPaths)) {
                throw new \Exception('يجب تحديد فيديو للنشر على TikTok');
            }

            $videoPath = public_path($mediaPaths[0]);
            $videoSize = filesize($videoPath);

            $initResponse = Http::withToken($account->access_token)
                ->post("{$this->baseUrl}/post/publish/video/init/", [
                    'post_info' => [
                        'title'         => $post->content,
                        'privacy_level' => 'PUBLIC_TO_EVERYONE',
                        'disable_duet'  => false,
                        'disable_stitch'=> false,
                        'disable_comment'=> false,
                    ],
                    'source_info' => [
                        'source'            => 'FILE_UPLOAD',
                        'video_size'        => $videoSize,
                        'chunk_size'        => $videoSize,
                        'total_chunk_count' => 1,
                    ],
                ]);

            if (!$initResponse->successful()) {
                throw new \Exception($initResponse->json()['error']['message'] ?? 'فشل بدء عملية الرفع');
            }

            $uploadData = $initResponse->json()['data'];
            $publishId  = $uploadData['publish_id'];
            $uploadUrl  = $uploadData['upload_url'];

            Http::withHeaders([
                'Content-Type'  => 'video/mp4',
                'Content-Range' => "bytes 0-" . ($videoSize - 1) . "/{$videoSize}",
                'Content-Length'=> $videoSize,
            ])->put($uploadUrl, fopen($videoPath, 'r'));

            return ['success' => true, 'platform_post_id' => $publishId];
        } catch (\Exception $e) {
            Log::error('TikTok publish error: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function refreshToken(SocialAccount $account): bool
    {
        if (!$account->refresh_token) return false;
        $response = Http::post('https://open.tiktokapis.com/v2/oauth/token/', [
            'client_key'    => config('services.tiktok.client_key'),
            'client_secret' => config('services.tiktok.client_secret'),
            'grant_type'    => 'refresh_token',
            'refresh_token' => $account->refresh_token,
        ]);
        if ($response->successful()) {
            $data = $response->json()['data'];
            $account->update([
                'access_token'    => $data['access_token'],
                'refresh_token'   => $data['refresh_token'],
                'token_expires_at'=> now()->addSeconds($data['expires_in']),
            ]);
            return true;
        }
        return false;
    }

    public function uploadImage(SocialAccount $account, string $imagePath): ?string { return null; }
    public function uploadVideo(SocialAccount $account, string $videoPath): ?string { return null; }
    public function getPostEngagement(SocialAccount $account, string $platformPostId): array { return []; }
}
