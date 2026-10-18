<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\front\AnalyticsController;
use App\Models\admin\SubCategory;
use App\Models\AnalyticsEvent;
use App\Models\AnalyticsVisit;
use App\Services\Analytics\Tracker;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

/** Journey analytics (client file «تعديلات», item 08): sources, funnel, abandonment and each visit's path. */
class AnalyticsAdminController extends Controller
{
    public const STATUSES = [
        'completed' => 'أتمّ طلبًا',
        'abandoned' => 'بدأ ولم يكمل',
        'browsing' => 'تصفّح فقط',
    ];

    /** Steps that mean the visitor started an order but did not finish it. */
    private const STARTED = ['form_start', 'cart_add', 'payment_start'];

    /** Events that make a visit «completed» besides a paid order: a request or a directly published project. */
    private const GOALS = ['request_submitted', 'project_published'];

    public function index(Request $request)
    {
        $filters = $this->filters($request);
        $visits = fn () => $this->query($filters);
        $visitIds = fn () => $this->query($filters)->select('analytics_visits.id');

        $total = $visits()->count();
        $completed = $this->status($visits(), 'completed')->count();
        $abandoned = $this->status($visits(), 'abandoned')->count();

        // Funnel: visits that reached each step (an event of that type), with the drop from the step before.
        $reached = AnalyticsEvent::whereIn('visit_id', $visitIds())->whereIn('type', array_keys(Tracker::FUNNEL))
            ->selectRaw('type, count(distinct visit_id) as visits')->groupBy('type')->pluck('visits', 'type');
        $funnel = [];
        $previous = null;
        foreach (Tracker::FUNNEL as $type => $label) {
            $count = (int) ($reached[$type] ?? 0);
            $funnel[] = [
                'type' => $type,
                'label' => $label,
                'count' => $count,
                'drop' => $previous ? round(100 * max($previous - $count, 0) / $previous) : null,
            ];
            $previous = $count;
        }

        $completedBySource = $this->status($visits(), 'completed')->selectRaw('source, count(*) as visits')->groupBy('source')->pluck('visits', 'source');
        $sources = $visits()->selectRaw('source, count(*) as visits')->groupBy('source')->orderByDesc('visits')->get()
            ->map(fn ($row) => [
                'source' => $row->source,
                'label' => Tracker::SOURCES[$row->source] ?? $row->source,
                'visits' => (int) $row->visits,
                'completed' => (int) ($completedBySource[$row->source] ?? 0),
            ]);

        // Abandonment: a reason only when the visitor gave one, otherwise «السبب غير معروف».
        $reasons = AnalyticsEvent::whereIn('visit_id', $visitIds())->where('type', 'abandon_reason')->get(['meta'])
            ->countBy(fn ($event) => $event->meta['reason'] ?? 'unknown');
        $unknown = max($abandoned - $this->status($visits(), 'abandoned')
            ->whereHas('events', fn ($q) => $q->where('type', 'abandon_reason'))->count(), 0);
        $abandonReasons = collect(AnalyticsController::ABANDON_REASONS)
            ->map(fn ($label, $key) => ['label' => $label, 'count' => (int) ($reasons[$key] ?? 0)])
            ->filter(fn ($row) => $row['count'] > 0)->values()
            ->push(['label' => 'السبب غير معروف', 'count' => $unknown]);

        // Homepage entry points (client file «المربعات الخمسة»): visits with each event.
        $entryTypes = ['home_card_view', 'home_card_click', 'project_form_start', 'project_published', 'diagnosis_completed', 'support_requested', 'freelancer_profile_completed'];
        $entryCounts = AnalyticsEvent::whereIn('visit_id', $visitIds())->whereIn('type', $entryTypes)
            ->selectRaw('type, count(distinct visit_id) as visits')->groupBy('type')->pluck('visits', 'type');
        $entries = collect($entryTypes)->map(fn ($type) => ['label' => Tracker::EVENTS[$type], 'count' => (int) ($entryCounts[$type] ?? 0)]);

        $list = $visits()->with(['events.service'])->orderByDesc('started_at')->paginate(25)->withQueryString();

        return view('admin.analytics.index', [
            'filters' => $filters,
            'total' => $total,
            'completed' => $completed,
            'abandoned' => $abandoned,
            'funnel' => $funnel,
            'sources' => $sources,
            'abandonReasons' => $abandonReasons,
            'entries' => $entries,
            'visits' => $list,
            'services' => SubCategory::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function show(AnalyticsVisit $visit)
    {
        $visit->load('events.service');

        return view('admin.analytics.show', [
            'visit' => $visit,
            'status' => $this->statusOf($visit),
            'reasons' => AnalyticsController::ABANDON_REASONS,
        ]);
    }

    /** completed | abandoned | browsing for one loaded visit. */
    public static function statusOf(AnalyticsVisit $visit): string
    {
        $types = $visit->events->pluck('type');

        return match (true) {
            $visit->last_step === 'order_complete' || $types->intersect(self::GOALS)->isNotEmpty() => 'completed',
            in_array($visit->last_step, self::STARTED, true) || $types->contains('project_form_start') => 'abandoned',
            default => 'browsing',
        };
    }

    private function filters(Request $request): array
    {
        $date = function ($value, Carbon $default) {
            try {
                return is_string($value) && $value !== '' ? Carbon::createFromFormat('Y-m-d', $value) : $default;
            } catch (\Throwable) {
                return $default;
            }
        };
        $from = $date($request->query('from'), now()->subDays(29))->startOfDay();
        $to = $date($request->query('to'), now())->endOfDay();
        if ($from->gt($to)) {
            [$from, $to] = [$to->copy()->startOfDay(), $from->copy()->endOfDay()];
        }

        return [
            'from' => $from,
            'to' => $to,
            'source' => array_key_exists((string) $request->string('source'), Tracker::SOURCES) ? (string) $request->string('source') : null,
            'service' => ctype_digit((string) $request->query('service')) ? (int) $request->query('service') : null,
            'status' => array_key_exists((string) $request->string('status'), self::STATUSES) ? (string) $request->string('status') : null,
        ];
    }

    private function query(array $filters): Builder
    {
        $query = AnalyticsVisit::query()->whereBetween('started_at', [$filters['from'], $filters['to']])
            ->when($filters['source'], fn ($q, $source) => $q->where('source', $source))
            ->when($filters['service'], fn ($q, $service) => $q->whereHas('events', fn ($e) => $e->where('sub_category_id', $service)));

        return $filters['status'] ? $this->status($query, $filters['status']) : $query;
    }

    private function status(Builder $query, string $status): Builder
    {
        $submitted = fn ($q) => $q->whereIn('type', self::GOALS);

        return match ($status) {
            'completed' => $query->where(fn ($q) => $q->where('last_step', 'order_complete')->orWhereHas('events', $submitted)),
            'abandoned' => $query->where(fn ($q) => $q->whereIn('last_step', self::STARTED)->orWhereHas('events', fn ($e) => $e->where('type', 'project_form_start')))
                ->whereDoesntHave('events', $submitted),
            default => $query->where(fn ($q) => $q->whereNull('last_step')->orWhere('last_step', 'service_view'))
                ->whereDoesntHave('events', fn ($e) => $e->whereIn('type', array_merge(self::GOALS, ['project_form_start']))),
        };
    }
}
