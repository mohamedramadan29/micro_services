<?php

namespace App\Services\Admin;

use App\Http\Controllers\admin\AnalyticsAdminController;
use App\Models\AnalyticsVisit;
use App\Models\Contract;
use App\Models\ContractDispute;
use App\Models\ContractStage;
use App\Models\HelpRequest;
use App\Models\OnlinePayment;
use App\Models\RequestPayment;
use App\Models\ServiceRequest;
use App\Models\SiteTest;
use App\Models\User;
use App\Models\WalletTransaction;
use App\Models\admin\Ticket;
use App\Models\front\Review;
use App\Models\front\WithDraw;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Cache;

/**
 * The admin home page: period figures compared with the period before, what waits for the team,
 * a daily chart and the latest activity. Figures are cached for 5 minutes per period.
 */
class DashboardStats
{
    public const PERIODS = ['today' => 'اليوم', '7' => '7 أيام', '30' => '30 يومًا', '90' => '90 يومًا'];

    public const PURPOSES = [
        OnlinePayment::PURPOSE_REQUEST => 'طلبات الخدمات',
        OnlinePayment::PURPOSE_CONTRACT_STAGE => 'مراحل المشاريع',
        OnlinePayment::PURPOSE_SERVICE_ORDER => 'شراء خدمات',
        OnlinePayment::PURPOSE_PRODUCT_ORDER => 'منتجات',
        OnlinePayment::PURPOSE_PACKAGE => 'باقات',
        OnlinePayment::PURPOSE_COURSE => 'كورسات',
        OnlinePayment::PURPOSE_BALANCE => 'شحن رصيد',
    ];

    public static function period(?string $key): string
    {
        return array_key_exists((string) $key, self::PERIODS) ? (string) $key : '30';
    }

    /** [from, to, previous from] for a period key. */
    public static function range(string $period): array
    {
        $now = CarbonImmutable::now();
        $from = $period === 'today' ? $now->startOfDay() : $now->subDays((int) $period - 1)->startOfDay();
        $length = $now->getTimestamp() - $from->getTimestamp();

        return [$from, $now, $from->subSeconds(max($length, 1))];
    }

    public function build(string $period): array
    {
        return Cache::remember('admin-dashboard:' . $period, 300, fn () => [
            'kpis' => $this->kpis($period),
            'now' => $this->now(),
            'chart' => $this->chart($period),
            'purposes' => $this->purposes($period),
            'services' => $this->topServices($period),
            'sources' => $this->sources($period),
            'built_at' => now(),
        ]) + [
            // Lists stay live: they are cheap and the team clicks into them.
            'attention' => $this->attention(),
            'latestRequests' => ServiceRequest::with('user:id,name')->latest()->limit(6)->get(),
            'latestUsers' => User::latest()->limit(6)->get(['id', 'name', 'email', 'account_type', 'freelancer_status', 'created_at']),
            'latestPayments' => OnlinePayment::with('user:id,name')->where('status', OnlinePayment::STATUS_PAID)->latest('paid_at')->limit(6)->get(),
        ];
    }

    /** Main figures with the change against the previous period of the same length. */
    private function kpis(string $period): array
    {
        [$from, $to, $prevFrom] = self::range($period);
        $both = function (callable $value) use ($from, $to, $prevFrom) {
            return ['value' => $value($from, $to), 'previous' => $value($prevFrom, $from)];
        };

        return [
            'revenue' => $both(fn ($a, $b) => (float) OnlinePayment::where('status', OnlinePayment::STATUS_PAID)->whereBetween('paid_at', [$a, $b])
                ->selectRaw('COALESCE(SUM(COALESCE(paid_amount, amount)), 0) as v')->value('v')),
            'payments' => $both(fn ($a, $b) => OnlinePayment::where('status', OnlinePayment::STATUS_PAID)->whereBetween('paid_at', [$a, $b])->count()),
            'commission' => $both(fn ($a, $b) => (float) WalletTransaction::where('type', WalletTransaction::TYPE_COMMISSION)->whereBetween('created_at', [$a, $b])->sum('amount')),
            'clients' => $both(fn ($a, $b) => User::where('account_type', User::TYPE_CLIENT)->whereBetween('created_at', [$a, $b])->count()),
            'freelancers' => $both(fn ($a, $b) => User::where('account_type', User::TYPE_FREELANCER)->whereBetween('created_at', [$a, $b])->count()),
            'requests' => $both(fn ($a, $b) => ServiceRequest::whereBetween('created_at', [$a, $b])->count()),
            'completed' => $both(fn ($a, $b) => ServiceRequest::where('status', ServiceRequest::STATUS_COMPLETED)->whereBetween('completed_at', [$a, $b])->count()
                + Contract::where('status', Contract::STATUS_COMPLETED)->whereNull('service_request_id')->whereBetween('completed_at', [$a, $b])->count()),
            'help' => $both(fn ($a, $b) => HelpRequest::whereBetween('created_at', [$a, $b])->count()),
            'site_tests' => $both(fn ($a, $b) => SiteTest::whereBetween('created_at', [$a, $b])->count()),
            'visits' => $both(fn ($a, $b) => AnalyticsVisit::whereBetween('started_at', [$a, $b])->count()),
            'converted' => $both(fn ($a, $b) => AnalyticsVisit::whereBetween('started_at', [$a, $b])
                ->where(fn ($q) => $q->where('last_step', 'order_complete')->orWhereHas('events', fn ($e) => $e->whereIn('type', AnalyticsAdminController::GOALS)))->count()),
            'offers_rate' => $both(function ($a, $b) {
                $total = ServiceRequest::whereBetween('created_at', [$a, $b])->where('status', '!=', ServiceRequest::STATUS_INQUIRY)->count();
                $withOffers = ServiceRequest::whereBetween('created_at', [$a, $b])->where('status', '!=', ServiceRequest::STATUS_INQUIRY)->has('offers')->count();

                return $total ? round($withOffers * 100 / $total) : null;
            }),
        ];
    }

    /** The state of the site right now (not tied to the period). */
    private function now(): array
    {
        return [
            'users' => User::count(),
            'clients' => User::where('account_type', User::TYPE_CLIENT)->count(),
            'freelancers' => User::where('account_type', User::TYPE_FREELANCER)->count(),
            'approved_freelancers' => User::where('account_type', User::TYPE_FREELANCER)->where('freelancer_status', User::FREELANCER_APPROVED)->count(),
            'open_requests' => ServiceRequest::where('status', ServiceRequest::STATUS_OPEN)->count(),
            'in_progress' => ServiceRequest::where('status', ServiceRequest::STATUS_IN_PROGRESS)->count(),
            'active_contracts' => Contract::whereIn('status', [Contract::STATUS_AGREEMENT, Contract::STATUS_ACTIVE])->count(),
            'held' => (float) RequestPayment::where('status', RequestPayment::STATUS_PAID)->selectRaw('COALESCE(SUM(amount - COALESCE(refunded_amount, 0)), 0) as v')->value('v')
                + (float) ContractStage::where('status', ContractStage::STATUS_FUNDED)->sum('amount'),
            'balances' => (float) User::sum('balance'),
            'pending_withdraw_amount' => (float) WithDraw::where('status', 0)->sum('amount'),
        ];
    }

    /** What the team should act on, most urgent first; only rows with something waiting are shown. */
    private function attention(): array
    {
        $rows = [
            ['خلافات بانتظار قرار', ContractDispute::where('status', ContractDispute::STATUS_AWAITING)->count(), route('admin.contracts.index', ['tab' => 'disputes']), true],
            ['دفعات تحتاج استرجاعًا', RequestPayment::where('status', RequestPayment::STATUS_CONFLICT)->count(), route('admin.requests.index', ['tab' => 'refunds']), true],
            ['مراحل متأخرة الآن', ContractStage::lateNow()->count(), route('admin.contracts.index', ['tab' => 'late']), true],
            ['طلبات سحب معلّقة', WithDraw::where('status', 0)->count(), url('admin/withdraws'), true],
            ['طلبات بلا عروض (متابعة)', ServiceRequest::whereNotNull('no_offer_alerted_at')->whereIn('status', [ServiceRequest::STATUS_OPEN, ServiceRequest::STATUS_INQUIRY])
                ->where(fn ($q) => $q->whereNull('followup_status')->orWhere('followup_status', '!=', ServiceRequest::FOLLOWUP_RESOLVED))->count(), route('admin.requests.index', ['tab' => 'followup']), true],
            ['استفسارات أسعار', ServiceRequest::where('status', ServiceRequest::STATUS_INQUIRY)->count(), route('admin.requests.index', ['tab' => 'inquiries']), false],
            ['طلبات مساعدة جديدة', HelpRequest::where('status', HelpRequest::STATUS_NEW)->count(), route('admin.help-requests.index'), true],
            ['مستقلون جاهزون للمراجعة والاعتماد', User::readyForReview()->count(), route('admin.freelancers.index', ['status' => 'ready']), true],
            ['خدمات مستقلين بانتظار النشر', \App\Models\admin\Service::where('status', \App\Models\admin\Service::STATUS_PENDING)->count(), url('admin/services?tab=pending'), false],
            ['أعمال مستقلين بانتظار النشر', \App\Models\front\UserPortfolio::where('status', \App\Models\front\UserPortfolio::STATUS_PENDING)->count(), route('admin.freelancer-works.index'), false],
            ['تذاكر دعم مفتوحة', Ticket::where('status', 1)->count(), url('admin/tickets'), false],
            ['خلافات مفتوحة (بانتظار رد الطرف الآخر)', ContractDispute::where('status', ContractDispute::STATUS_OPEN)->count(), route('admin.contracts.index', ['tab' => 'disputes']), false],
            ['تقييمات بانتظار الموافقة', Review::where('is_approved', false)->count(), route('admin.reviews.index'), false],
            ['تقارير فحص مواقع طلبها العملاء', SiteTest::where('review_status', SiteTest::REVIEW_REQUESTED)->count(), route('admin.site-tests.index'), false],
        ];

        return array_values(array_filter(array_map(fn ($r) => ['label' => $r[0], 'count' => $r[1], 'url' => $r[2], 'hot' => $r[3]], $rows), fn ($r) => $r['count'] > 0));
    }

    /** Daily (or hourly for «today») series of new users, new requests and paid amounts. */
    private function chart(string $period): array
    {
        [$from, $to] = self::range($period);
        $hourly = $period === 'today';
        $buckets = [];
        for ($t = $from; $t <= $to; $t = $hourly ? $t->addHour() : $t->addDay()) {
            $buckets[$t->format($hourly ? 'Y-m-d H' : 'Y-m-d')] = 0;
        }
        $series = function ($query, string $column, ?string $sum = null) use ($buckets, $hourly, $from, $to) {
            $out = $buckets;
            $query->whereBetween($column, [$from, $to])->get([$column] + ($sum ? [1 => $sum] : []))
                ->each(function ($row) use (&$out, $column, $sum, $hourly) {
                    $key = $row->{$column}?->format($hourly ? 'Y-m-d H' : 'Y-m-d');
                    if ($key !== null && array_key_exists($key, $out)) {
                        $out[$key] += $sum ? (float) $row->{$sum} : 1;
                    }
                });

            return array_values($out);
        };

        return [
            'labels' => array_map(fn ($k) => $hourly ? substr($k, 11) . ':00' : CarbonImmutable::parse($k)->format('d/m'), array_keys($buckets)),
            'users' => $series(User::query(), 'created_at'),
            'requests' => $series(ServiceRequest::query(), 'created_at'),
            'revenue' => $series(OnlinePayment::where('status', OnlinePayment::STATUS_PAID), 'paid_at', 'amount'),
        ];
    }

    private function purposes(string $period): array
    {
        [$from, $to] = self::range($period);

        return OnlinePayment::where('status', OnlinePayment::STATUS_PAID)->whereBetween('paid_at', [$from, $to])
            ->selectRaw('purpose, COUNT(*) as n, SUM(amount) as total')->groupBy('purpose')->orderByDesc('total')->get()
            ->map(fn ($r) => ['label' => self::PURPOSES[$r->purpose] ?? $r->purpose, 'count' => (int) $r->n, 'total' => (float) $r->total])->all();
    }

    private function topServices(string $period): array
    {
        [$from, $to] = self::range($period);

        return ServiceRequest::whereBetween('created_at', [$from, $to])
            ->selectRaw("COALESCE(service_name, '—') as name, COUNT(*) as n")->groupBy('service_name')->orderByDesc('n')->limit(6)->get()
            ->map(fn ($r) => ['label' => $r->name, 'count' => (int) $r->n])->all();
    }

    private function sources(string $period): array
    {
        [$from, $to] = self::range($period);

        return ServiceRequest::whereBetween('created_at', [$from, $to])
            ->selectRaw('source, COUNT(*) as n')->groupBy('source')->orderByDesc('n')->get()
            ->map(fn ($r) => ['label' => ServiceRequest::SOURCES[$r->source] ?? 'الأسئلة الموجّهة', 'count' => (int) $r->n])->all();
    }
}
