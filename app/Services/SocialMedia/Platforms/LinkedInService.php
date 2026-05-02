<?php

namespace App\Services\SocialMedia\Platforms;

use App\Models\SocialAccount;
use App\Models\SocialPost;
use App\Services\SocialMedia\Contracts\SocialPlatformInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class LinkedInService implements SocialPlatformInterface
{
    private string $baseUrl = 'https://api.linkedin.com/v2';

    public function getAuthUrl(): string
    {
        $params = http_build_query([
            'response_type' => 'code',
            'client_id'     => config('services.linkedin.client_id'),
            'redirect_uri'  => config('services.linkedin.redirect'),
            'state'         => csrf_token(),
            'scope'         => 'w_member_social r_liteprofile',
        ]);

        return "https://www.linkedin.com/oauth/v2/authorization?{$params}";
    }

    public function handleCallback(string $code): array
    {
        $response = Http::asForm()->post('https://www.linkedin.com/oauth/v2/accessToken', [
            'grant_type'    => 'authorization_code',
            'code'          => $code,
            'client_id'     => config('services.linkedin.client_id'),
            'client_secret' => config('services.linkedin.client_secret'),
            'redirect_uri'  => config('services.linkedin.redirect'),
        ]);

        if (!$response->successful()) {
            throw new \Exception('فشل الحصول على Access Token من LinkedIn');
        }

        $data = $response->json();
        $accessToken = $data['access_token'];

        // الحصول على بيانات الملف الشخصي
        $profileResponse = Http::withToken($accessToken)->get("{$this->baseUrl}/me");
        $profile = $profileResponse->json();

        return [
            'access_token'  => $accessToken,
            'expires_in'    => $data['expires_in'] ?? 5184000,
            'account_id'    => $profile['id'],
            'account_name'  => ($profile['localizedFirstName'] ?? 'LinkedIn') . ' ' . ($profile['localizedLastName'] ?? 'User'),
            'avatar'        => null, // يحتاج إلى تصريح إضافي للحصول على الصورة
        ];
    }

    public function publish(SocialAccount $account, SocialPost $post): array
    {
        try {
            $urn = "urn:li:person:{$account->account_id}";
            
            $payload = [
                'author' => $urn,
                'lifecycleState' => 'PUBLISHED',
                'specificContent' => [
                    'com.linkedin.ugc.ShareContent' => [
                        'shareCommentary' => [
                            'text' => $post->content,
                        ],
                        'shareMediaCategory' => 'NONE',
                    ],
                ],
                'visibility' => [
                    'com.linkedin.ugc.MemberNetworkVisibility' => 'PUBLIC',
                ],
            ];

            // إذا كان هناك صور
            if ($post->media_type === 'image' && !empty($post->media_paths)) {
                // ملاحظة: LinkedIn يتطلب خطوة "Register Upload" أولاً ثم الرفع الفعلي.
                // للتبسيط في هذا الكود، سنقوم بالنشر النصي أو التعامل معه كـ Share بسيط.
                // يمكنك تطوير هذا الجزء لرفع الميديا فعلياً.
            }

            $response = Http::withToken($account->access_token)
                ->post("{$this->baseUrl}/ugcPosts", $payload);

            if (!$response->successful()) {
                throw new \Exception($response->json()['message'] ?? 'فشل النشر على LinkedIn');
            }

            return [
                'success' => true,
                'platform_post_id' => $response->json()['id'],
            ];

        } catch (\Exception $e) {
            Log::error('LinkedIn publish error: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function uploadImage(SocialAccount $account, string $imagePath): ?string { return null; }
    public function uploadVideo(SocialAccount $account, string $videoPath): ?string { return null; }
    public function refreshToken(SocialAccount $account): bool { return false; }
    public function getPostEngagement(SocialAccount $account, string $platformPostId): array { return []; }
}
