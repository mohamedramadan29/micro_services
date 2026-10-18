<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\AnalyticsEvent;
use App\Models\ContentBlock;
use App\Services\Analytics\Tracker;
use App\Services\Content\ContentAreas;
use App\Services\Content\ContentManager;
use App\Services\Content\HomeActions;
use App\Support\StaffPermissions;
use Illuminate\Http\Request;

/**
 * «إدارة مربعات الصفحة الرئيسية» (client file «المربعات الخمسة»): the cards at a glance with
 * their destination, audience, visibility and results (views, clicks, completed goals of the
 * visits that clicked). Texts are edited in «إدارة محتوى الواجهة» (draft → preview → publish).
 */
class HomeCardAdminController extends Controller
{
    public const PERIODS = [7 => 'آخر 7 أيام', 30 => 'آخر 30 يومًا', 90 => 'آخر 90 يومًا'];

    public function index(Request $request, ContentManager $content)
    {
        $days = array_key_exists((int) $request->query('days'), self::PERIODS) ? (int) $request->query('days') : 30;
        // Results per campaign source (utm / ads / search…) of the visits.
        $source = array_key_exists((string) $request->string('source'), Tracker::SOURCES) ? (string) $request->string('source') : null;
        $rows = $this->rows($content, now()->subDays($days), $source);

        if ($request->query('export') === 'csv') {
            return $this->csv($rows, $days);
        }

        return view('admin.home-cards.index', [
            'rows' => $rows,
            'days' => $days,
            'periods' => self::PERIODS,
            'source' => $source,
            'sources' => Tracker::SOURCES,
            'access' => StaffPermissions::contentAccess($request->user()),
            'hasDrafts' => $content->hasDrafts('home.cards') || $content->hasDrafts('home.paths'),
            'commission' => (float) (\App\Models\admin\Setting::first()?->website_commission ?? 10),
        ]);
    }

    private function rows(ContentManager $content, $from, ?string $source = null)
    {
        $bySource = fn ($q, string $column = 'visit_id') => $source
            ? $q->whereIn($column, \App\Models\AnalyticsVisit::where('source', $source)->select('id')) : $q;

        return $content->blocks('home.cards')->map(function (ContentBlock $block) use ($from, $bySource) {
            $fields = $block->working();
            $views = $bySource(AnalyticsEvent::where('type', 'home_card_view')->where('created_at', '>=', $from)->whereJsonContains('meta->cards', $block->id))->count();
            $clicks = $bySource(AnalyticsEvent::where('type', 'home_card_click')->where('created_at', '>=', $from)->where('meta->card', $block->id))->count();
            $completed = $bySource(AnalyticsEvent::from('analytics_events as g'), 'g.visit_id')
                ->whereIn('g.type', Tracker::CARD_GOALS)
                ->whereExists(fn ($q) => $q->from('analytics_events as c')
                    ->whereColumn('c.visit_id', 'g.visit_id')
                    ->where('c.type', 'home_card_click')
                    ->where('c.meta->card', $block->id)
                    ->where('c.created_at', '>=', $from)
                    ->whereColumn('c.created_at', '<=', 'g.created_at'))
                ->distinct()->count('g.visit_id');

            return [
                'block' => $block,
                'title' => $fields['title'] ?? '',
                'group' => ContentAreas::CARD_GROUPS[$fields['group'] ?? 'primary'] ?? '',
                'group_key' => $fields['group'] ?? 'primary',
                'action' => HomeActions::ALL[$fields['action'] ?? '']['label'] ?? ($fields['link'] ?? '—'),
                'path' => HomeActions::url($fields['action'] ?? null),
                'audience' => ContentAreas::CARD_AUDIENCES[$fields['audience'] ?? 'all'] ?? 'الجميع',
                'visible' => ($fields['_visible'] ?? true) && ! $block->isDeletedInDraft(),
                'draft' => $block->hasDraft(),
                'views' => $views,
                'clicks' => $clicks,
                'ctr' => $views ? round($clicks * 100 / $views, 1) : null,
                'completed' => $completed,
            ];
        })->sortBy(fn ($row) => [$row['group_key'] === 'secondary' ? 1 : 0, (int) ($row['block']->working()['_order'] ?? 0)])->values();
    }

    private function csv($rows, int $days)
    {
        $lines = [['المربع', 'المجموعة', 'الوجهة', 'يظهر لـ', 'ظاهر', 'مرات الظهور', 'النقرات', 'نسبة النقر %', 'مكتمل']];
        foreach ($rows as $row) {
            $lines[] = [$row['title'], $row['group'], $row['action'], $row['audience'], $row['visible'] ? 'نعم' : 'لا', $row['views'], $row['clicks'], $row['ctr'] ?? '', $row['completed']];
        }
        $out = "\u{FEFF}" . collect($lines)->map(fn ($line) => collect($line)->map(fn ($v) => '"' . str_replace('"', '""', (string) $v) . '"')->implode(','))->implode("\r\n");

        return response($out, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="home-cards-' . $days . 'd-' . now()->format('Y-m-d') . '.csv"',
        ]);
    }
}
