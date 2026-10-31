<?php

namespace App\Support;

use App\Models\Contract;
use App\Models\RequestOffer;
use App\Models\User;
use App\Models\admin\Service;
use App\Models\front\OrderDetail;
use App\Models\front\UserPortfolio;
use Illuminate\Http\Request;

/**
 * The account menu (dashboard side column + navbar dropdown), built for the account type:
 * a client sees his requests and purchases, a freelancer his work; a client's old services/sales show
 * only when he already has some. Properties, maintenance, jobs and courses are not linked (pages kept).
 */
class AccountMenu
{
    private static array $memo = [];

    /** [['title' => ?string, 'items' => [['label', 'href', 'icon', 'active', 'badge']]]] */
    public static function sections(User $user, Request $request): array
    {
        $key = $user->id . '|' . $request->path();
        if (isset(self::$memo[$key])) {
            return self::$memo[$key];
        }

        $has = self::usage($user);
        $item = fn (string $label, string $href, string $icon, array $active, int $badge = 0) => [
            'label' => $label, 'href' => $href, 'icon' => $icon, 'badge' => $badge,
            'active' => collect($active)->contains(fn ($pattern) => str_contains($pattern, '.') && ! str_contains($pattern, '/') ? $request->routeIs($pattern) : $request->is($pattern)),
        ];

        $sections = [];
        // A freelancer's own profile steps (email, WhatsApp, photo, bio) are all on «تعديل الحساب»;
        // specializations and portfolio have their own pages.
        $missing = $user->isFreelancer()
            ? count(array_filter(array_intersect_key($user->freelancerChecklist(), array_flip(['email', 'whatsapp', 'photo', 'profile'])), fn ($done) => ! $done))
            : 0;
        $account = [
            $item('لوحتي', url('dashboard'), 'bi bi-speedometer2', ['dashboard']),
            $item('تعديل الحساب', url('update-account'), 'bi bi-gear-fill', ['update-account'], $missing),
        ];
        if ($user->isFreelancer()) {
            $account[] = $item('تخصصاتي', route('specializations.edit'), 'bi bi-tags-fill', ['specializations.*']);
        }
        if ($user->isFreelancer() || $has['portfolio']) {
            $account[] = $item('أعمالي (معرض الأعمال)', url('portfolio/user/index'), 'bi bi-images', ['portfolio/user*']);
        }
        $sections[] = ['title' => 'حسابي', 'items' => $account];

        if ($user->isFreelancer()) {
            $work = [
                $item('طلبات العملاء', route('freelancer.requests.index'), 'bi bi-inboxes-fill', ['freelancer.requests.*']),
                $item('عروضي', route('freelancer.offers.index'), 'bi bi-send-check-fill', ['freelancer.offers.*', 'offers.*']),
            ];
            if ($has['contracts']) {
                $work[] = $item('مشاريعي', route('contracts.index'), 'bi bi-kanban-fill', ['contracts.*']);
            }
            $work[] = $item('خدماتي المعروضة', url('service/index'), 'bi bi-grid-fill', ['service/index', 'service/add', 'service/update/*']);
            if ($has['sold']) {
                $work[] = $item('طلبات شراء خدماتي', url('orders'), 'bi bi-bag-check-fill', ['orders']);
            }
            $sections[] = ['title' => 'عملي كمستقل', 'items' => $work];
        } else {
            $requests = [
                $item('اطلب خدمة جديدة', route('requests.create'), 'bi bi-plus-circle-fill', ['requests.create', 'requests.staged']),
                $item('طلباتي', route('requests.index'), 'bi bi-clipboard-check-fill', ['requests.index', 'requests.show', 'requests.edit', 'projects.edit', 'offers.*'], $has['new_offers']),
            ];
            if ($has['contracts']) {
                $requests[] = $item('مشاريعي', route('contracts.index'), 'bi bi-kanban-fill', ['contracts.*']);
            }
            if ($has['bought']) {
                $requests[] = $item('خدمات اشتريتها', url('purches'), 'bi bi-bag-check-fill', ['purches']);
            }
            // «نفّذها للأعمال»: once the account has a company request or project.
            if ($has['business']) {
                $requests[] = $item('نفّذها للأعمال (شركتي)', route('business.mine'), 'bi bi-buildings-fill', ['business.mine', 'business.requests.*', 'business.projects.*'], $has['business_reviews']);
            }
            $sections[] = ['title' => 'طلباتي ومشاريعي', 'items' => $requests];
        }

        $money = [
            $item('الرصيد والسحب', url('balance'), 'bi bi-wallet2', ['balance']),
            $item('مشتريات المنتجات', url('my/products/purches'), 'bi bi-cart-check-fill', ['my/products/purches']),
            $item('المحادثات', url('chats'), 'bi bi-chat-dots-fill', ['chats', 'chat-main/*']),
            $item('الدعم الفني (تذاكري)', url('tickets'), 'bi bi-life-preserver', ['tickets', 'ticket/*']),
            $item('اقتراحاتي', route('suggestions.mine'), 'bi bi-lightbulb-fill', ['suggestions.mine', 'suggestions.show']),
        ];
        $sections[] = ['title' => 'الرصيد والتواصل', 'items' => $money];

        // Old sections: only for accounts that already have something there.
        $other = [];
        if (! $user->isFreelancer() && $has['services']) {
            $other[] = $item('خدماتي المعروضة', url('service/index'), 'bi bi-grid-fill', ['service/index', 'service/add', 'service/update/*']);
        }
        if (! $user->isFreelancer() && $has['sold']) {
            $other[] = $item('طلبات شراء خدماتي', url('orders'), 'bi bi-bag-check-fill', ['orders']);
        }
        if ($user->isFreelancer() && $has['bought']) {
            $other[] = $item('خدمات اشتريتها', url('purches'), 'bi bi-bag-check-fill', ['purches']);
        }
        // Properties, maintenance and jobs are old sections: their pages still work by URL,
        // but no menu links to them (user decision 2026-10-18, like the courses).
        if ($other) {
            $sections[] = ['title' => 'أقسام أخرى', 'items' => $other];
        }

        return self::$memo[$key] = $sections;
    }

    /** Badge total for the navbar avatar (what waits for this user). */
    public static function badgeTotal(User $user, Request $request): int
    {
        return collect(self::sections($user, $request))->flatMap(fn ($s) => $s['items'])->sum('badge');
    }

    private static function usage(User $user): array
    {
        $safe = function (callable $query) {
            try {
                return $query();
            } catch (\Throwable $e) {
                report($e);

                return false;
            }
        };

        return [
            'contracts' => $safe(fn () => Contract::involving($user)->exists()),
            'business' => $safe(fn () => \App\Models\BusinessRequest::where('user_id', $user->id)->exists()),
            'business_reviews' => (int) $safe(fn () => \App\Models\BusinessDelivery::where('status', \App\Models\BusinessDelivery::STATUS_PENDING)
                ->whereHas('project', fn ($q) => $q->where('user_id', $user->id))->count()),
            'portfolio' => $safe(fn () => UserPortfolio::where('user_id', $user->id)->exists()),
            'services' => $safe(fn () => Service::where('user_id', $user->id)->exists()),
            'sold' => $safe(fn () => OrderDetail::where('user_seller', $user->id)->exists()),
            'bought' => $safe(fn () => OrderDetail::where('user_buyer', $user->id)->exists()),
            'new_offers' => $user->isFreelancer() ? 0 : (int) $safe(fn () => RequestOffer::where('status', RequestOffer::STATUS_NEW)
                ->whereHas('request', fn ($q) => $q->where('user_id', $user->id))->count()),
        ];
    }
}
