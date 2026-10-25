<?php

namespace App\Support;

use App\Models\ContractDispute;
use App\Models\HelpRequest;
use App\Models\RequestPayment;
use App\Models\ServiceRequest;
use App\Models\User;
use App\Models\admin\Ticket;
use App\Models\front\WithDraw;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;

/**
 * The admin sidebar: groups in working order (users first), the current page marked
 * server-side (its group opens by itself) and counters of what waits for the team.
 */
class AdminMenu
{
    /** [label, icon, items: [label, href, url patterns that make it active, badge key|null]] */
    public static function groups(): array
    {
        return [
            ['المستخدمون', 'fa-users', [
                ['كل المستخدمين', 'admin/users', ['admin/users*'], null],
                ['المستقلون والاعتماد', 'admin.freelancers.index', ['admin/freelancers*'], 'freelancers'],
                ['أعمال المستقلين', 'admin.freelancer-works.index', ['admin/freelancer-works*'], 'works'],
                ['العملاء وتصنيفاتهم', 'admin.clients.index', ['admin/clients*'], null],
                ['أدوار الموظفين', 'admin.staff.index', ['admin/staff-roles*'], null],
            ]],
            ['طلبات الخدمات', 'fa-clipboard-list', [
                ['الطلبات', 'admin.requests.index', ['admin/requests', 'admin/requests/*', 'admin/request-offers*'], 'requests'],
                ['طلبات المساعدة', 'admin.help-requests.index', ['admin/help-requests*'], 'help'],
                ['اختبارات المواقع', 'admin.site-tests.index', ['admin/site-tests', 'admin/site-tests/*'], null],
                ['نماذج الخدمات', 'admin.request-forms.index', ['admin/request-forms*'], null],
                ['دليل الاستخدام', 'admin.requests.guide', ['admin/requests-guide*'], null],
            ]],
            ['المشاريع بمراحل', 'fa-file-contract', [
                ['العقود والخلافات', 'admin.contracts.index', ['admin/contracts*'], 'disputes'],
                ['سياسة المشاريع (المهل والقوالب)', 'admin.contract-policy.index', ['admin/contract-policy*'], null],
            ]],
            ['المبيعات والأموال', 'fa-wallet', [
                ['طلبات شراء الخدمات', 'admin/service-orders/index', ['admin/service-orders*', 'admin/service-order/*'], null],
                ['طلبات المنتجات', 'admin/orders', ['admin/orders*', 'admin/order/*'], null],
                ['طلبات السحب', 'admin/withdraws', ['admin/withdraws*', 'admin/withdraw/*'], 'withdraws'],
                ['الباقات', 'admin/packages', ['admin/packages*', 'admin/package/*'], null],
                ['تصنيف الباقات', 'admin/package-titles', ['admin/package-titles*', 'admin/package-title/*'], null],
            ]],
            ['الدعم والتواصل', 'fa-headset', [
                ['تذاكر الدعم الفني', 'admin/tickets', ['admin/tickets*', 'admin/ticket/*'], 'tickets'],
                ['رسائل المستخدمين', 'admin/chats', ['admin/chats*', 'admin/chat/*'], null],
                ['تقييمات العملاء', 'admin.reviews.index', ['admin/reviews*', 'admin/review/*'], 'reviews'],
            ]],
            ['الخدمات والمنتجات', 'fa-th-large', [
                ['الخدمات (صفحة الخدمات)', 'admin.catalog.services', ['admin/catalog/services*', 'admin/sub-categories*'], null],
                ['مجموعات الخدمات', 'admin.catalog.groups', ['admin/catalog/groups*', 'admin/categories*'], null],
                ['خدمات المستقلين', 'admin/services', ['admin/services*', 'admin/service/*'], 'services'],
                ['المنتجات الرقمية', 'admin.products.index', ['admin/products*', 'admin/product/*'], null],
                ['أعمال نفّذها', 'admin/nafizha-portfolio', ['admin/nafizha-portfolio*'], null],
            ]],
            ['واجهة الموقع', 'fa-paint-brush', [
                ['مربعات الصفحة الرئيسية', 'admin.home-cards.index', ['admin/home-cards*'], null],
                ['إدارة محتوى الواجهة', 'admin.content.index', ['admin/content*'], null],
                ['صفحات الهبوط', 'admin.landing.index', ['admin/landing-pages*'], null],
                ['مكتبة الصور والخلفيات', 'admin.media.index', ['admin/media*'], null],
                ['أقسام المدونة', 'admin/blog_category', ['admin/blog_category*'], null],
                ['مقالات المدونة', 'admin/blogs', ['admin/blogs*', 'admin/blog/*'], null],
            ]],
            ['التسويق والتقارير', 'fa-chart-line', [
                ['إحصائيات رحلة الزوار', 'admin.analytics.index', ['admin/analytics*'], null],
                ['SEO والكلمات المفتاحية', 'admin.seo.index', ['admin/seo*'], null],
                ['حملات البريد', 'admin.email.campaigns.index', ['admin/email/campaigns*'], null],
                ['القوائم البريدية', 'admin.email.lists.index', ['admin/email/lists*', 'admin/email/sheets*'], null],
                ['قوالب الإيميلات', 'admin.email.templates.index', ['admin/email/templates*'], null],
                ['تقارير البريد', 'admin.email.analytics', ['admin/email/analytics*'], null],
                ['السوشيال ميديا', 'admin.social.index', ['admin/social-media', 'admin/social-media/posts*', 'admin/social-media/post*'], null],
                ['ربط حسابات السوشيال', 'admin.social.accounts', ['admin/social-media/accounts*', 'admin/social-media/connect*', 'admin/social-media/callback*'], null],
            ]],
            ['الإشعارات', 'fa-bell', [
                ['نصوص الإشعارات', 'admin.notification-texts.index', ['admin/notification-texts*'], null],
                ['سجل الإشعارات (وصل / فشل)', 'admin.notification-log.index', ['admin/notification-log*'], null],
            ]],
            ['أقسام أخرى', 'fa-layer-group', [
                ['الكورسات الجديدة', 'admin.new-courses.index', ['admin/new-courses*', 'admin/new-course/*'], null],
                ['الكورسات القديمة', 'admin/courses', ['admin/courses*', 'admin/course/*'], null],
                ['صفحات الكورسات العامة', 'admin/public-courses/index', ['admin/public-courses*'], null],
                ['العقارات', 'admin/properities', ['admin/properities*', 'admin/properity/*'], null],
                ['خدمات الصيانة', 'admin/properity-maintain', ['admin/properity-maintain*'], null],
                ['الوظائف', 'admin/jobs', ['admin/jobs*', 'admin/job/*'], null],
                ['الاستشاريون', 'admin/consultants', ['admin/consultants*', 'admin/consultant/*'], null],
            ]],
            ['الإعدادات', 'fa-cog', [
                ['الإعدادات العامة للموقع', 'admin/public_settings', ['admin/public_settings*'], null],
                ['إعدادات اختبار المواقع', 'admin.site-tests.settings', ['admin/site-tests/settings*'], null],
                ['إعدادات Gmail API', 'admin.email.gmail.settings', ['admin/email/gmail*'], null],
                ['تعديل بياناتي', 'admin/update_admin_details', ['admin/update_admin_details*'], null],
                ['تغيير كلمة المرور', 'admin/update_admin_password', ['admin/update_admin_password*'], null],
            ]],
        ];
    }

    /** Groups ready to render: hrefs resolved, active item and group marked, badges counted. */
    public static function build(Request $request): array
    {
        $badges = self::badges();
        $groups = [];
        // The most specific match wins (e.g. «إعدادات اختبار المواقع» over «اختبارات المواقع»).
        $best = null;
        $bestLength = -1;
        foreach (self::groups() as $g => [$label, $icon, $items]) {
            foreach ($items as $i => [$itemLabel, $target, $patterns, $badge]) {
                foreach ($patterns as $pattern) {
                    if ($request->is($pattern) && strlen($pattern) > $bestLength) {
                        [$best, $bestLength] = [[$g, $i], strlen($pattern)];
                    }
                }
            }
        }

        foreach (self::groups() as $g => [$label, $icon, $items]) {
            $rows = [];
            $total = 0;
            foreach ($items as $i => [$itemLabel, $target, $patterns, $badge]) {
                $count = $badge ? ($badges[$badge] ?? 0) : 0;
                $total += $count;
                $rows[] = [
                    'label' => $itemLabel,
                    'href' => Route::has($target) ? route($target) : url($target),
                    'active' => $best === [$g, $i],
                    'badge' => $count,
                ];
            }
            $groups[] = ['label' => $label, 'icon' => $icon, 'items' => $rows, 'active' => ($best[0] ?? null) === $g, 'badge' => $total];
        }

        return $groups;
    }

    /** What waits for the team; cached a minute so every admin page stays fast. */
    public static function badges(): array
    {
        return Cache::remember('admin-menu-badges', 60, fn () => self::safe(fn () => [
            // Only freelancers who finished their profile wait for the team (the others are still filling it in).
            'freelancers' => User::readyForReview()->count(),
            'works' => \App\Models\front\UserPortfolio::where('status', \App\Models\front\UserPortfolio::STATUS_PENDING)->count(),
            'services' => \App\Models\admin\Service::where('status', \App\Models\admin\Service::STATUS_PENDING)->count(),
            'requests' => ServiceRequest::whereNotNull('no_offer_alerted_at')->where(fn ($q) => $q->whereNull('followup_status')->orWhere('followup_status', '!=', ServiceRequest::FOLLOWUP_RESOLVED))
                ->whereIn('status', [ServiceRequest::STATUS_OPEN, ServiceRequest::STATUS_INQUIRY])->count()
                + RequestPayment::where('status', RequestPayment::STATUS_CONFLICT)->count(),
            'help' => HelpRequest::where('status', HelpRequest::STATUS_NEW)->count(),
            'disputes' => ContractDispute::whereIn('status', [ContractDispute::STATUS_OPEN, ContractDispute::STATUS_AWAITING])->count(),
            'withdraws' => WithDraw::where('status', 0)->count(),
            'tickets' => Ticket::where('status', 1)->count(),
            'reviews' => \App\Models\front\Review::where('is_approved', false)->count(),
        ]));
    }

    private static function safe(callable $counts): array
    {
        try {
            return $counts();
        } catch (\Throwable $e) {
            report($e);

            return [];
        }
    }
}
