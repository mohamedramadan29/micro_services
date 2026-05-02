<?php

namespace App\Services\SocialMedia\Platforms;

use App\Models\SocialAccount;
use App\Models\SocialPost;
use App\Services\SocialMedia\Contracts\SocialPlatformInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TwitterService implements SocialPlatformInterface
{
    private string $baseUrl = 'https://api.twitter.com/2';

    public function getAuthUrl(): string
    {
        // Twitter OAuth 2.0 يتطلب PKCE غالباً، ولكن سنستخدم الطريقة الأساسية إذا كان التطبيق معداً لذلك
        $params = http_build_query([
            'response_type'         => 'code',
            'client_id'             => config('services.twitter.client_id'),
            'redirect_uri'          => config('services.twitter.redirect'),
            'scope'                 => 'tweet.read tweet.write users.read offline.access',
            'state'                 => csrf_token(),
            'code_challenge'        => 'challenge', // يجب توليده ديناميكياً للـ PKCE
            'code_challenge_method' => 'plain',
        ]);

        return "https://twitter.com/i/oauth2/authorize?{$params}";
    }

    public function handleCallback(string $code): array
    {
        $response = Http::asForm()
            ->withBasicAuth(config('services.twitter.client_id'), config('services.twitter.client_secret'))
            ->post('https://api.twitter.com/2/oauth2/token', [
                'code'          => $code,
                'grant_type'    => 'authorization_code',
                'redirect_uri'  => config('services.twitter.redirect'),
                'code_verifier' => 'challenge',
            ]);

        if (!$response->successful()) {
            throw new \Exception('فشل الحصول على Access Token من Twitter: ' . $response->body());
        }

        $data = $response->json();
        $accessToken = $data['access_token'];

        // الحصول على بيانات المستخدم
        $userResponse = Http::withToken($accessToken)->get("{$this->baseUrl}/users/me", [
            'user.fields' => 'profile_image_url'
        ]);
        $userData = $userResponse->json()['data'];

        return [
            'access_token'  => $accessToken,
            'refresh_token' => $data['refresh_token'] ?? null,
            'expires_in'    => $data['expires_in'] ?? 7200,
            'account_id'    => $userData['id'],
            'account_name'  => $userData['username'],
            'avatar'        => $userData['profile_image_url'] ?? null,
        ];
    }

    public function publish(SocialAccount $account, SocialPost $post): array
    {
        try {
            $payload = [
                'text' => $post->content,
            ];

            // إذا كان هناك ميديا، نحتاج لرفعها أولاً عبر v1.1 API ثم إرفاق الـ Media ID
            // حالياً سنقوم بالنشر النصي فقط
            
            $response = Http::withToken($account->access_token)
                ->post("{$this->baseUrl}/tweets", $payload);

            if (!$response->successful()) {
                throw new \Exception($response->json()['detail'] ?? 'فشل النشر على Twitter');
            }

            return [
                'success' => true,
                'platform_post_id' => $response->json()['data']['id'],
            ];

        } catch (\Exception $e) {
            Log::error('Twitter publish error: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function refreshToken(SocialAccount $account): bool
    {
        if (!$account->refresh_token) return false;

        $response = Http::asForm()
            ->withBasicAuth(config('services.twitter.client_id'), config('services.twitter.client_secret'))
            ->post('https://api.twitter.com/2/oauth2/token', [
                'grant_type'    => 'refresh_token',
                'refresh_token' => $account->refresh_token,
            ]);

        if ($response->successful()) {
            $data = $response->json();
            $account->update([
                'access_token'    => $data['access_token'],
                'refresh_token'   => $data['refresh_token'] ?? $account->refresh_token,
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
