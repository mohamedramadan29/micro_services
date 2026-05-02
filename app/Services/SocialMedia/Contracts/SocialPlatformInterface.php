<?php

namespace App\Services\SocialMedia\Contracts;

use App\Models\SocialAccount;
use App\Models\SocialPost;

interface SocialPlatformInterface
{
    /**
     * نشر بوست على المنصة
     */
    public function publish(SocialAccount $account, SocialPost $post): array;

    /**
     * رفع صورة
     */
    public function uploadImage(SocialAccount $account, string $imagePath): ?string;

    /**
     * رفع فيديو
     */
    public function uploadVideo(SocialAccount $account, string $videoPath): ?string;

    /**
     * الحصول على بيانات OAuth URL
     */
    public function getAuthUrl(): string;

    /**
     * تبادل الـ code بـ access token
     */
    public function handleCallback(string $code): array;

    /**
     * تجديد الـ access token
     */
    public function refreshToken(SocialAccount $account): bool;

    /**
     * الحصول على إحصائيات البوست
     */
    public function getPostEngagement(SocialAccount $account, string $platformPostId): array;
}
