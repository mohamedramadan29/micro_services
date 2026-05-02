<?php

namespace App\Services\SocialMedia;

use App\Models\SocialAccount;
use App\Models\SocialPost;
use App\Models\SocialPostResult;
use App\Services\SocialMedia\Platforms\FacebookService;
use App\Services\SocialMedia\Platforms\InstagramService;
use App\Services\SocialMedia\Platforms\TikTokService;
use App\Services\SocialMedia\Platforms\YouTubeService;
use App\Services\SocialMedia\Platforms\TwitterService;
use App\Services\SocialMedia\Platforms\LinkedInService;
use App\Services\SocialMedia\Contracts\SocialPlatformInterface;
use Illuminate\Support\Facades\Log;

class SocialMediaService
{
    private array $services = [];

    public function __construct(
        private FacebookService  $facebook,
        private InstagramService $instagram,
        private TikTokService    $tiktok,
        private YouTubeService   $youtube,
        private TwitterService   $twitter,
        private LinkedInService  $linkedin,
    ) {
        $this->services = [
            'facebook'  => $this->facebook,
            'instagram' => $this->instagram,
            'tiktok'    => $this->tiktok,
            'youtube'   => $this->youtube,
            'twitter'   => $this->twitter,
            'linkedin'  => $this->linkedin,
        ];
    }

    /**
     * الحصول على service المنصة المطلوبة
     */
    public function platform(string $platform): SocialPlatformInterface
    {
        if (!isset($this->services[$platform])) {
            throw new \Exception("المنصة '{$platform}' غير مدعومة");
        }
        return $this->services[$platform];
    }

    /**
     * نشر بوست على جميع المنصات المحددة
     */
    public function publishPost(SocialPost $post): void
    {
        $platforms = $post->platforms ?? [];
        $post->update(['status' => 'publishing']);

        $results = [];

        foreach ($platforms as $platformName) {
            $account = SocialAccount::where('platform', $platformName)
                ->where('is_active', true)
                ->first();

            if (!$account) {
                Log::warning("لا يوجد حساب نشط لـ {$platformName}");
                $results[] = [
                    'post_id'    => $post->id,
                    'account_id' => 0,
                    'platform'   => $platformName,
                    'status'     => 'failed',
                    'error_message' => 'لا يوجد حساب مربوط وناشط لهذه المنصة',
                ];
                continue;
            }

            // تجديد التوكن إذا كان منتهياً
            if ($account->isTokenExpired()) {
                $this->platform($platformName)->refreshToken($account);
                $account->refresh();
            }

            try {
                $service = $this->platform($platformName);
                $result  = $service->publish($account, $post);

                $results[] = [
                    'post_id'          => $post->id,
                    'account_id'       => $account->id,
                    'platform'         => $platformName,
                    'platform_post_id' => $result['platform_post_id'] ?? null,
                    'status'           => $result['success'] ? 'published' : 'failed',
                    'error_message'    => $result['error'] ?? null,
                    'published_at'     => $result['success'] ? now() : null,
                ];
            } catch (\Exception $e) {
                Log::error("Error publishing to {$platformName}: " . $e->getMessage());
                $results[] = [
                    'post_id'       => $post->id,
                    'account_id'    => $account->id,
                    'platform'      => $platformName,
                    'status'        => 'failed',
                    'error_message' => $e->getMessage(),
                ];
            }
        }

        // حفظ نتائج النشر
        SocialPostResult::insert($results);

        // تحديث حالة البوست
        $published = collect($results)->where('status', 'published')->count();
        $failed    = collect($results)->where('status', 'failed')->count();

        $post->update([
            'status'       => $published === 0 ? 'failed' : ($failed > 0 ? 'partial' : 'published'),
            'published_at' => $published > 0 ? now() : null,
        ]);
    }

    /**
     * الحصول على رابط OAuth للمنصة
     */
    public function getAuthUrl(string $platform): string
    {
        return $this->platform($platform)->getAuthUrl();
    }

    /**
     * معالجة OAuth Callback
     */
    public function handleCallback(string $platform, string $code): array
    {
        return $this->platform($platform)->handleCallback($code);
    }

    /**
     * تحديث إحصائيات البوستات
     */
    public function syncEngagement(SocialPostResult $result): void
    {
        if (!$result->platform_post_id || $result->status !== 'published') return;

        $account = $result->account;
        if (!$account) return;

        try {
            $engagement = $this->platform($result->platform)
                ->getPostEngagement($account, $result->platform_post_id);
            $result->update(['engagement' => $engagement]);
        } catch (\Exception $e) {
            Log::error("Engagement sync error for {$result->platform}: " . $e->getMessage());
        }
    }
}
