<?php

namespace App\Services\Content;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/**
 * Where a homepage card may lead (client file «المربعات الخمسة»): the admin picks one
 * of these internal destinations, never types a URL. Each one is a named route, so a
 * renamed path keeps working.
 */
class HomeActions
{
    public const ALL = [
        'request' => ['label' => 'اطلب عرضًا (الأسئلة الديناميكية)', 'route' => 'requests.create'],
        'diagnose' => ['label' => 'شخّص مشروعك (التشخيص + فحص الموقع)', 'route' => 'diagnose'],
        'staged' => ['label' => 'مشروعك ما بيوقف (مشروع بمراحل)', 'route' => 'requests.staged'],
        'post_project' => ['label' => 'انشر مشروعك (النشر المباشر)', 'route' => 'projects.new'],
        'freelancer_join' => ['label' => 'انضم كمستقل', 'route' => 'freelancers.join'],
        'site_test' => ['label' => 'اختبر موقعك مجانًا', 'route' => 'site-tests.index'],
        'help' => ['label' => 'ساعدوني أختار (صفحة الخدمات)', 'route' => 'services.index', 'query' => ['help' => 1]],
        'services' => ['label' => 'صفحة الخدمات', 'route' => 'services.index'],
        'ready_services' => ['label' => 'خدمات جاهزة بسعر ثابت', 'route' => 'services.ready'],
        'works' => ['label' => 'أعمال المستقلين', 'route' => 'portfolios.index'],
        'freelancers' => ['label' => 'دليل المستقلين', 'route' => 'freelancers.index'],
        'none' => ['label' => 'بدون رابط («قريبًا»)', 'route' => null],
    ];

    public static function options(): array
    {
        return array_map(fn ($a) => $a['label'], self::ALL);
    }

    public static function url(?string $action): ?string
    {
        $definition = self::ALL[$action] ?? null;
        if (! $definition || ! $definition['route'] || ! Route::has($definition['route'])) {
            return null;
        }

        return route($definition['route'], $definition['query'] ?? []);
    }

    /** The button text for the visitor: a freelancer who already joined sees «أكمل ملفك». */
    public static function cta(array $card): string
    {
        $user = Auth::user();
        if (($card['action'] ?? null) === 'freelancer_join' && $user?->isFreelancer()) {
            return $user->isApprovedFreelancer() ? 'ملفي كمستقل' : 'أكمل ملفك كمستقل';
        }

        return $card['button_text'] ?: 'ابدأ الآن';
    }
}
