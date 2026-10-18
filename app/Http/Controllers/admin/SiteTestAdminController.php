<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\admin\SubCategory;
use App\Models\SiteTest;
use App\Models\SiteTestIssue;
use App\Models\SiteTestRule;
use App\Models\User;
use App\Notifications\SiteTestReportReady;
use App\Services\SiteTests\SiteTestSettings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\Rule;

/** Admin side of «اختبر موقعك مجاناً»: list, review and send the report, rules and settings (spec pages 8–10). */
class SiteTestAdminController extends Controller
{
    /** List filters, in the spec's order (page 9). */
    public const FILTERS = [
        'new' => 'جديد',
        'running' => 'قيد الفحص',
        'failed' => 'تعذر',
        'report' => 'طلب التقرير',
        'interested' => 'مهتم بالتنفيذ',
        'declined' => 'لا يريد الآن',
        'in_review' => 'قيد المراجعة',
        'published' => 'تم النشر',
        'sent' => 'أُرسل التقرير',
        'closed' => 'مغلق',
        'norule' => 'بلا خدمة مرتبطة',
    ];

    public function index(Request $request)
    {
        $filter = array_key_exists((string) $request->string('filter'), self::FILTERS) ? (string) $request->string('filter') : null;
        $tests = SiteTest::with(['issues', 'suggestedService', 'assignee', 'serviceRequest'])
            ->when($filter, fn ($q) => match ($filter) {
                'new' => $q->where('status', SiteTest::STATUS_DONE)->where('review_status', SiteTest::REVIEW_NONE)->whereNull('choice'),
                'running' => $q->whereIn('status', [SiteTest::STATUS_QUEUED, SiteTest::STATUS_RUNNING]),
                'failed' => $q->where('status', SiteTest::STATUS_FAILED),
                'report' => $q->where('review_status', SiteTest::REVIEW_REQUESTED),
                'interested' => $q->where('choice', 'yes')->whereNull('service_request_id')->where('review_status', '!=', SiteTest::REVIEW_CLOSED),
                'declined' => $q->where('choice', 'no'),
                'in_review' => $q->where('review_status', SiteTest::REVIEW_IN_PROGRESS),
                'published' => $q->whereNotNull('service_request_id'),
                'sent' => $q->where('review_status', SiteTest::REVIEW_SENT),
                'closed' => $q->where('review_status', SiteTest::REVIEW_CLOSED),
                'norule' => $q->where('status', SiteTest::STATUS_DONE)->whereNull('suggested_sub_category_id')
                    ->whereHas('issues', fn ($i) => $i->where('shown', true)->where('source', 'tool')),
            })
            ->when($request->filled('q'), function ($query) use ($request) {
                $term = '%' . str_replace(['%', '_'], ['\%', '\_'], $request->query('q')) . '%';
                $query->where(fn ($q) => $q->where('number', 'like', $term)->orWhere('url', 'like', $term)
                    ->orWhere('contact_name', 'like', $term)->orWhere('contact_phone', 'like', $term)->orWhere('contact_email', 'like', $term));
            })
            ->latest('id')
            ->paginate(25)
            ->withQueryString();

        return view('admin.site-tests.index', compact('tests', 'filter'));
    }

    public function show(SiteTest $siteTest)
    {
        $siteTest->load(['issues.service', 'results', 'suggestedService', 'assignee', 'serviceRequest', 'user', 'parent']);
        $services = SubCategory::ordered()->get(['id', 'name']);
        $staff = User::activeAdmins()->orderBy('name')->get(['id', 'name']);
        $history = SiteTest::where('host', $siteTest->host)->whereKeyNot($siteTest->id)->latest('id')->limit(10)->get(['id', 'number', 'created_at', 'status']);

        return view('admin.site-tests.show', ['test' => $siteTest, 'services' => $services, 'staff' => $staff, 'history' => $history]);
    }

    /** Team review: summary, suggested service, assignee and review state. */
    public function review(Request $request, SiteTest $siteTest)
    {
        $data = $request->validate([
            'review_summary' => 'nullable|string|max:5000',
            'team_notes' => 'nullable|string|max:5000',
            'suggested_sub_category_id' => ['nullable', 'integer', Rule::exists('sub_categories', 'id')],
            'assigned_to' => ['nullable', 'integer', Rule::exists('users', 'id')->where('account_type', User::TYPE_ADMIN)],
            'review_status' => ['required', Rule::in(array_keys(SiteTest::REVIEW_STATUSES))],
        ]);
        // «أُرسل التقرير» is set only by send(); a sent report can still be closed or reopened for review.
        if ($data['review_status'] === SiteTest::REVIEW_SENT && $siteTest->review_status !== SiteTest::REVIEW_SENT) {
            unset($data['review_status']);
        }
        if ($siteTest->service_request_id || $siteTest->choice === 'yes') {
            unset($data['suggested_sub_category_id']); // the client already accepted (or got a request for) this service
        }
        $siteTest->update($data);

        return back()->with('success', 'تم حفظ المراجعة');
    }

    /** Send the reviewed report: by email when the client gave one; the WhatsApp button is on the page. */
    public function send(SiteTest $siteTest)
    {
        if (! $siteTest->review_summary) {
            return back()->withErrors('اكتب ملخص المراجعة أولًا.');
        }
        $siteTest->update(['review_status' => SiteTest::REVIEW_SENT, 'report_sent_at' => now()]);

        $emailed = false;
        if ($siteTest->contact_email) {
            try {
                Notification::route('mail', $siteTest->contact_email)->notify(new SiteTestReportReady($siteTest));
                $emailed = true;
            } catch (\Throwable $e) {
                Log::warning('Site test report email failed', ['test' => $siteTest->id, 'error' => $e->getMessage()]);
            }
        }

        return back()->with('success', 'تم تعليم التقرير كمُرسل' . ($emailed ? ' وإرسال رابطه للعميل بالبريد.' : '. أرسل الرابط للعميل عبر واتساب.'));
    }

    public function addIssue(Request $request, SiteTest $siteTest)
    {
        $data = $request->validate([
            'title' => 'required|string|max:250',
            'description' => 'nullable|string|max:1000',
            'sub_category_id' => ['nullable', 'integer', Rule::exists('sub_categories', 'id')],
        ], ['title.required' => 'اكتب الملاحظة']);
        $siteTest->issues()->create($data + [
            'source' => 'team',
            'priority' => 'team',
            'page_url' => $siteTest->url,
            'sort_order' => (int) $siteTest->issues()->max('sort_order') + 1,
        ]);

        return back()->with('success', 'أُضيفت ملاحظة الفريق (تظهر للعميل كمراجعة بشرية منفصلة عن نتيجة الأداة)');
    }

    public function toggleIssue(SiteTest $siteTest, SiteTestIssue $issue)
    {
        abort_unless($issue->site_test_id === $siteTest->id, 404);
        $issue->update(['shown' => ! $issue->shown]);

        return back()->with('success', $issue->shown ? 'المشكلة ظاهرة للعميل' : 'أُخفيت المشكلة عن العميل');
    }

    public function raw(SiteTest $siteTest, int $result)
    {
        $row = $siteTest->results()->findOrFail($result);

        return response($row->raw ?? '', 200, ['Content-Type' => 'application/json; charset=utf-8', 'Content-Disposition' => 'attachment; filename="' . $siteTest->number . '-' . $row->device . '.json"']);
    }

    public function settings()
    {
        return view('admin.site-tests.settings', [
            'settings' => SiteTestSettings::all(),
            'rules' => SiteTestRule::with('service')->orderBy('audit_code')->get(),
            'services' => SubCategory::ordered()->get(['id', 'name']),
            'knownCodes' => \App\Models\SiteTestIssue::query()->distinct()->orderBy('audit_code')->pluck('audit_code')->filter()->values(),
        ]);
    }

    public function saveSettings(Request $request)
    {
        $data = $request->validate([
            'categories' => 'required|array|min:1',
            'categories.*' => Rule::in(array_keys(SiteTestSettings::CATEGORIES)),
            'devices' => 'required|array|min:1',
            'devices.*' => Rule::in(array_keys(SiteTest::DEVICES)),
            'max_issues' => 'required|integer|min:1|max:3',
            'hourly_limit' => 'required|integer|min:1|max:100',
            'retention_days' => 'nullable|integer|min:0|max:3650',
            'failure_message' => 'required|string|max:500',
        ]);
        $data['retention_days'] = (int) ($data['retention_days'] ?? SiteTestSettings::get('retention_days'));
        SiteTestSettings::save($data + [
            'enabled' => $request->boolean('enabled'),
            'show_offer_immediately' => $request->boolean('show_offer_immediately'),
        ]);

        return back()->with('success', 'تم حفظ إعدادات الفحص');
    }

    /** The questions of the full-report form: edit, reorder (by position), hide, add, delete. */
    public function saveQuestions(Request $request)
    {
        $data = $request->validate([
            'questions' => 'nullable|array|max:10',
            'questions.*.key' => 'nullable|string|max:40',
            'questions.*.label' => 'nullable|string|max:255',
            'questions.*.type' => ['nullable', Rule::in(array_keys(SiteTestSettings::QUESTION_TYPES))],
            'questions.*.options' => 'nullable|string|max:2000',
            'questions.*.order' => 'nullable|integer|min:0|max:99',
            'questions.*.show_if' => 'nullable|string|max:40',
            'questions.*.show_value' => 'nullable|string|max:255',
        ]);

        $questions = [];
        $keys = [];
        foreach ($data['questions'] ?? [] as $index => $row) {
            $label = trim((string) ($row['label'] ?? ''));
            if ($label === '' || $request->boolean("questions.$index.delete")) {
                continue;
            }
            $type = $row['type'] ?? 'text';
            $options = array_values(array_unique(array_filter(array_map('trim', preg_split('/\r?\n/', (string) ($row['options'] ?? ''))))));
            if ($type === 'select' && count($options) < 2) {
                return back()->withInput()->withErrors('السؤال «' . $label . '» من نوع «اختيار» يحتاج خيارين على الأقل (كل خيار في سطر).');
            }
            // Existing questions keep their key (old answers stay linked); new ones get a fresh one.
            $key = preg_match('/^[a-z0-9_]{1,40}$/', (string) ($row['key'] ?? '')) ? $row['key'] : 'q' . substr(md5($label . microtime()), 0, 8);
            if (in_array($key, $keys, true)) {
                $key .= '_' . $index;
            }
            $keys[] = $key;
            $questions[] = ['key' => $key, 'label' => $label, 'type' => $type, 'options' => $type === 'select' ? $options : [],
                'required' => $request->boolean("questions.$index.required"), 'active' => $request->boolean("questions.$index.active"),
                // Shown only when another question has this answer (e.g. «نوع الموقع» = «متجر»).
                'show_if' => ($row['show_if'] ?? '') ?: null, 'show_value' => trim((string) ($row['show_value'] ?? '')) ?: null,
                'order' => (int) ($row['order'] ?? $index)];
        }
        usort($questions, fn ($a, $b) => $a['order'] <=> $b['order']);
        $keys = array_column($questions, 'key');
        foreach ($questions as &$question) {
            unset($question['order']);
            if ($question['show_if'] && (! in_array($question['show_if'], $keys, true) || $question['show_if'] === $question['key'])) {
                $question['show_if'] = $question['show_value'] = null; // a condition on a missing question is dropped
            }
        }
        unset($question);
        SiteTestSettings::save(['questions' => $questions]);

        return back()->with('success', 'تم حفظ أسئلة نموذج التقرير');
    }

    public function saveRule(Request $request)
    {
        $data = $request->validate([
            'audit_code' => ['required', 'string', 'max:100', 'regex:/^[a-z0-9-]+$/'],
            'label' => 'nullable|string|max:255',
            'sub_category_id' => ['required', 'integer', Rule::exists('sub_categories', 'id')],
            'priority' => ['required', Rule::in(['high', 'medium'])],
        ], [
            'audit_code.regex' => 'رمز النتيجة كما يكتبه المحرك، مثل meta-description',
            'sub_category_id.required' => 'اختر الخدمة',
        ]);
        SiteTestRule::updateOrCreate(['audit_code' => $data['audit_code']], $data + ['active' => $request->boolean('active', true)]);

        return back()->with('success', 'تم حفظ قاعدة الربط');
    }

    public function deleteRule(SiteTestRule $rule)
    {
        $rule->delete();

        return back()->with('success', 'تم حذف القاعدة');
    }
}
