<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\admin\SubCategory;
use App\Models\SeoKeyword;
use App\Models\SeoSearchQuery;
use App\Services\Seo\SeoKeywordService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;

/** «إدارة SEO والكلمات المفتاحية» (client file «تعديلات», item 05). */
class SeoKeywordController extends Controller
{
    public const SORTS = [
        'newest' => 'الأحدث',
        'keyword' => 'الكلمة',
        'impressions' => 'الظهور في Google',
        'clicks' => 'النقرات',
        'position' => 'متوسط الترتيب',
    ];

    /** CSV columns, in order (the header row of the export and of a file to import). */
    public const CSV_COLUMNS = ['keyword' => 'الكلمة المفتاحية', 'page_url' => 'الصفحة المرتبطة', 'service' => 'الخدمة', 'status' => 'الحالة', 'notes' => 'ملاحظات'];

    public const IMPORT_MAX_ROWS = 20000;

    public function __construct(private SeoKeywordService $seo)
    {
    }

    public function index(Request $request)
    {
        $keywords = $this->filtered($request)->with('service:id,name')->paginate(50)->withQueryString();
        $counts = SeoKeyword::selectRaw('status, COUNT(*) as total')->groupBy('status')->pluck('total', 'status');

        return view('admin.seo.index', [
            'keywords' => $keywords,
            'counts' => $counts,
            'total' => SeoKeyword::count(),
            'services' => SubCategory::ordered()->get(['id', 'name']),
            'connected' => $this->seo->connected(),
            'lastSync' => $this->seo->lastSync(),
        ]);
    }

    /** What people searched on Google to reach the site, and whether each is tracked. */
    public function queries(Request $request)
    {
        $queries = SeoSearchQuery::query()
            ->when($request->filled('q'), fn ($q) => $q->where('query', 'like', '%' . $this->like($request->query('q')) . '%'))
            ->orderByDesc('impressions')
            ->paginate(50)
            ->withQueryString();
        $tracked = SeoKeyword::whereIn('keyword', $queries->getCollection()->map(fn ($r) => SeoKeyword::normalize($r->query)))->pluck('keyword')->all();

        return view('admin.seo.queries', [
            'queries' => $queries,
            'tracked' => $tracked,
            'connected' => $this->seo->connected(),
            'lastSync' => $this->seo->lastSync(),
        ]);
    }

    /** One keyword per line: hundreds can be pasted at once. */
    public function store(Request $request)
    {
        $data = $request->validate([
            'keywords' => 'required|string|max:200000',
            'page_url' => $this->pageRule(),
            'sub_category_id' => ['nullable', 'integer', Rule::exists('sub_categories', 'id')],
            'status' => ['nullable', Rule::in(array_keys(SeoKeyword::STATUSES))],
            'notes' => 'nullable|string|max:2000',
        ], $this->messages());

        $lines = collect(preg_split('/\R/u', $data['keywords']))
            ->map(fn ($k) => SeoKeyword::normalize($k))
            ->filter(fn ($k) => $k !== '' && mb_strlen($k) <= 250)
            ->unique();
        $existing = SeoKeyword::whereIn('keyword', $lines)->pluck('keyword')->all();
        $new = $lines->diff($existing);
        $status = $data['status'] ?? (($data['page_url'] ?? null) || ($data['sub_category_id'] ?? null) ? 'linked' : 'unlinked');

        foreach ($new->chunk(500) as $chunk) {
            SeoKeyword::insert($chunk->map(fn ($keyword) => [
                'keyword' => $keyword,
                'page_url' => $data['page_url'] ?? null,
                'sub_category_id' => $data['sub_category_id'] ?? null,
                'status' => $status,
                'notes' => $data['notes'] ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ])->values()->all());
        }

        return back()->with('success', 'أُضيفت ' . $new->count() . ' كلمة' . ($existing ? '، وتُركت ' . count($existing) . ' موجودة من قبل' : '') . '.');
    }

    public function update(Request $request, SeoKeyword $keyword)
    {
        $data = $request->validate([
            'keyword' => 'required|string|max:250',
            'page_url' => $this->pageRule(),
            'sub_category_id' => ['nullable', 'integer', Rule::exists('sub_categories', 'id')],
            'status' => ['required', Rule::in(array_keys(SeoKeyword::STATUSES))],
            'notes' => 'nullable|string|max:2000',
        ], $this->messages());
        $data['keyword'] = SeoKeyword::normalize($data['keyword']);
        if (SeoKeyword::where('keyword', $data['keyword'])->whereKeyNot($keyword->id)->exists()) {
            return back()->withErrors('هذه الكلمة موجودة بالفعل.');
        }
        if ($data['keyword'] !== $keyword->keyword) {
            // Another word: Google's figures of the old one do not apply.
            $data += ['gsc_clicks' => null, 'gsc_impressions' => null, 'gsc_ctr' => null, 'gsc_position' => null, 'gsc_synced_at' => null];
        }
        $keyword->update($data);

        return back()->with('success', 'تم حفظ الكلمة.');
    }

    public function destroy(Request $request)
    {
        $ids = array_filter(array_map('intval', (array) $request->input('ids', [])));
        if (! $ids) {
            return back()->withErrors('اختر كلمة واحدة على الأقل.');
        }
        $deleted = SeoKeyword::whereIn('id', $ids)->delete();

        return back()->with('success', 'حُذفت ' . $deleted . ' كلمة.');
    }

    public function track(Request $request)
    {
        $data = $request->validate(['query' => 'required|string|max:250']);
        $keyword = SeoKeyword::firstOrCreate(['keyword' => SeoKeyword::normalize($data['query'])], ['status' => 'unlinked']);
        $row = SeoSearchQuery::where('query', $data['query'])->first();
        if ($row && $keyword->wasRecentlyCreated) {
            $keyword->update(['gsc_clicks' => $row->clicks, 'gsc_impressions' => $row->impressions, 'gsc_ctr' => $row->ctr, 'gsc_position' => $row->position, 'gsc_synced_at' => $row->synced_at]);
        }

        return back()->with('success', 'أُضيفت «' . $keyword->keyword . '» إلى الكلمات المتابَعة.');
    }

    public function export(Request $request): StreamedResponse
    {
        $query = $this->filtered($request)->with('service:id,name');

        return response()->streamDownload(function () use ($query) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // UTF-8 BOM so Excel shows Arabic correctly
            fputcsv($out, array_merge(array_values(self::CSV_COLUMNS), ['النقرات', 'مرات الظهور', 'نسبة النقر', 'متوسط الترتيب', 'آخر تحديث من Google']));
            $query->lazyById(1000)->each(function (SeoKeyword $k) use ($out) {
                $safe = fn ($v) => is_string($v) && preg_match('/^[=+\-@\t\r]/', $v) ? "'" . $v : $v; // no spreadsheet formulas
                fputcsv($out, [
                    $safe($k->keyword), $safe($k->page_url), $k->service?->name, $k->statusLabel(), $safe($k->notes),
                    $k->gsc_clicks, $k->gsc_impressions, $k->gsc_ctr !== null ? round((float) $k->gsc_ctr * 100, 2) . '%' : null,
                    $k->gsc_position, $k->gsc_synced_at?->format('Y-m-d'),
                ]);
            });
            fclose($out);
        }, 'seo-keywords-' . now()->format('Y-m-d') . '.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /** CSV with the export's columns (or just keywords, one per row). Existing keywords are updated. */
    public function import(Request $request)
    {
        $request->validate(['file' => 'required|file|mimes:csv,txt|max:10240'], [
            'file.required' => 'اختر ملف CSV',
            'file.mimes' => 'الملف يجب أن يكون CSV',
            'file.max' => 'أقصى حجم 10 ميجابايت',
        ]);

        $handle = fopen($request->file('file')->getRealPath(), 'r');
        $services = SubCategory::pluck('id', 'name');
        $statusByLabel = array_flip(SeoKeyword::STATUSES) + array_combine(array_keys(SeoKeyword::STATUSES), array_keys(SeoKeyword::STATUSES));
        $added = $updated = $skipped = 0;
        $columns = ['keyword']; // without a header row: one keyword per row
        $line = 0;

        while (($row = fgetcsv($handle)) !== false && $line <= self::IMPORT_MAX_ROWS) {
            $line++;
            $row = array_map(fn ($v) => trim((string) $v), $row);
            if ($line === 1) {
                $row[0] = preg_replace('/^\xEF\xBB\xBF/', '', $row[0] ?? '');
                // A header row names the columns (Arabic as exported, or the English keys).
                $header = array_map(fn ($h) => array_search($h, self::CSV_COLUMNS, true) ?: (isset(self::CSV_COLUMNS[$h]) ? $h : null), $row);
                if (in_array('keyword', $header, true)) {
                    $columns = $header;
                    continue;
                }
            }
            $values = [];
            foreach ($columns as $index => $field) {
                if ($field) {
                    $values[$field] = $row[$index] ?? '';
                }
            }
            $keyword = SeoKeyword::normalize($values['keyword'] ?? '');
            $page = $values['page_url'] ?? '';
            if ($keyword === '' || mb_strlen($keyword) > 250 || ($page !== '' && ! preg_match('#^(/[^\s]*|https?://[^\s]+)$#i', $page))) {
                $skipped++;
                continue;
            }

            $fields = array_filter([
                'page_url' => $page ?: null,
                'sub_category_id' => isset($values['service']) && $values['service'] !== '' ? ($services[$values['service']] ?? null) : null,
                'status' => $statusByLabel[$values['status'] ?? ''] ?? null,
                'notes' => isset($values['notes']) && $values['notes'] !== '' ? mb_substr($values['notes'], 0, 2000) : null,
            ], fn ($v) => $v !== null);

            $existing = SeoKeyword::where('keyword', $keyword)->first();
            if ($existing) {
                $existing->update($fields);
                $updated++;
            } else {
                SeoKeyword::create(['keyword' => $keyword, 'status' => ($fields['page_url'] ?? $fields['sub_category_id'] ?? null) ? 'linked' : 'unlinked'] + $fields);
                $added++;
            }
        }
        fclose($handle);

        return back()->with('success', "الاستيراد: أُضيفت {$added}، وتحدّثت {$updated}" . ($skipped ? "، وتُخطّيت {$skipped} (فارغة أو رابط غير صحيح)" : '') . '.');
    }

    public function sync()
    {
        if (! $this->seo->connected()) {
            return back()->withErrors('Search Console غير مربوط بعد. اتبع الخطوات الظاهرة في الصفحة.');
        }
        try {
            $count = $this->seo->sync();
        } catch (\Throwable $e) {
            Log::warning('Search Console sync failed', ['error' => $e->getMessage()]);

            return back()->withErrors('تعذّر جلب البيانات من Google: ' . mb_substr($e->getMessage(), 0, 200));
        }

        return back()->with('success', 'تم التحديث من Google Search Console: ' . $count . ' عبارة بحث.');
    }

    private function filtered(Request $request)
    {
        $sort = array_key_exists((string) $request->string('sort'), self::SORTS) ? (string) $request->string('sort') : 'newest';

        $query = SeoKeyword::query()
            ->when($request->filled('q'), fn ($q) => $q->where(fn ($w) => $w->where('keyword', 'like', '%' . $this->like($request->query('q')) . '%')
                ->orWhere('page_url', 'like', '%' . $this->like($request->query('q')) . '%')))
            ->when(array_key_exists((string) $request->string('status'), SeoKeyword::STATUSES), fn ($q) => $q->where('status', $request->query('status')))
            ->when($request->integer('service'), fn ($q, $service) => $q->where('sub_category_id', $service))
            ->when($request->query('data') === 'yes', fn ($q) => $q->where('gsc_impressions', '>', 0))
            ->when($request->query('data') === 'no', fn ($q) => $q->where(fn ($w) => $w->whereNull('gsc_impressions')->orWhere('gsc_impressions', 0)));

        return match ($sort) {
            'keyword' => $query->orderBy('keyword'),
            'impressions' => $query->orderByRaw('gsc_impressions IS NULL')->orderByDesc('gsc_impressions'),
            'clicks' => $query->orderByRaw('gsc_clicks IS NULL')->orderByDesc('gsc_clicks'),
            'position' => $query->orderByRaw('gsc_position IS NULL')->orderBy('gsc_position'),
            default => $query->orderByDesc('id'),
        };
    }

    private function pageRule(): array
    {
        return ['nullable', 'string', 'max:500', 'regex:#^(/[^\s]*|https?://[^\s]+)$#i'];
    }

    private function messages(): array
    {
        return [
            'keywords.required' => 'اكتب كلمة واحدة على الأقل (كل كلمة في سطر)',
            'keyword.required' => 'اكتب الكلمة',
            'page_url.regex' => 'الصفحة تبدأ بـ / أو https://',
        ];
    }

    private function like(string $value): string
    {
        return str_replace(['%', '_'], ['\%', '\_'], $value);
    }
}
