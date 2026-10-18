<?php

namespace App\Support;

use App\Models\ContentMedia;
use App\Services\Content\ContentManager;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

/** View helpers for admin-managed content (see App\Services\Content). */
class SiteContent
{
    public static function items(string $area): Collection
    {
        return app(ContentManager::class)->items($area);
    }

    public static function first(string $area): array
    {
        return app(ContentManager::class)->first($area);
    }

    public static function media($id): ?ContentMedia
    {
        return app(ContentManager::class)->mediaItem($id);
    }

    public static function previewing(): bool
    {
        return app(ContentManager::class)->previewing();
    }

    /** A stored link ("/request", "#steps" or "https://…") as a URL. */
    public static function url(?string $link): ?string
    {
        if (! $link) {
            return null;
        }

        return preg_match('#^(https?://|\#)#i', $link) ? $link : url($link);
    }

    /** Whether the current page is the link's page (the locale prefix is ignored). */
    public static function isActive(?string $link): bool
    {
        if (! $link || preg_match('#^(https?://|\#)#i', $link)) {
            return false;
        }
        $path = trim(preg_replace('#^(ar|en)(/|$)#', '', request()->path()), '/');
        $target = trim($link, '/');

        return $target === '' ? $path === '' : ($path === $target || str_starts_with($path, $target . '/'));
    }

    /** Who a menu link is shown to (ContentAreas::AUDIENCES). */
    public static function shownTo(?string $audience): bool
    {
        $user = Auth::user();

        return match ($audience) {
            'freelancer' => (bool) $user?->isFreelancer(),
            'not_freelancer' => ! $user?->isFreelancer(),
            'auth' => (bool) $user,
            'guest' => ! $user,
            'client' => (bool) $user?->isClient(),
            default => true,
        };
    }
}
