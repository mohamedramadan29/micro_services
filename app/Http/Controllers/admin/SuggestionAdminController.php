<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Suggestion;
use App\Models\SuggestionCategory;
use App\Models\User;
use App\Services\Suggestions\SuggestionService;
use App\Support\StaffPermissions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * «الاقتراحات» (client file «نظام الاقتراحات»): list, filters, details, internal notes, status with reason,
 * assignment, priority, duplicate links, audit log, CSV export and the types (settings).
 * Abilities are checked here on the server (StaffPermissions::canSuggestions), not only by hiding buttons.
 */
class SuggestionAdminController extends Controller
{
    public function index(Request $request)
    {
        $this->authorizeAbility('view');

        $filters = $request->only(['q', 'status', 'category', 'role', 'from', 'to']);
        $suggestions = $this->filtered($request)->with(['category', 'user', 'assignee'])->withCount('duplicates')
            ->latest('id')->paginate(25)->withQueryString();

        $counts = Suggestion::selectRaw('status, COUNT(*) as total')->groupBy('status')->pluck('total', 'status');
        $categories = SuggestionCategory::orderBy('sort_order')->orderBy('id')->get();

        return view('admin.suggestions.index', compact('suggestions', 'filters', 'counts', 'categories') + [
            'canExport' => StaffPermissions::canSuggestions(Auth::user(), 'export'),
            'canSettings' => StaffPermissions::canSuggestions(Auth::user(), 'settings'),
        ]);
    }

    public function show(Suggestion $suggestion)
    {
        $this->authorizeAbility('view');
        $suggestion->load(['category', 'user', 'assignee', 'duplicateOf', 'duplicates', 'notes.user', 'history.user']);

        return view('admin.suggestions.show', [
            'suggestion' => $suggestion,
            // Only staff who can open and handle suggestions are offered as assignees.
            'staff' => StaffPermissions::suggestionStaff('manage')->orderBy('name')->get(['id', 'name']),
            'canManage' => StaffPermissions::canSuggestions(Auth::user(), 'manage'),
            // The sender's contact data only for those who handle suggestions.
            'canSeeContact' => StaffPermissions::canSuggestions(Auth::user(), 'manage'),
        ]);
    }

    public function update(Request $request, Suggestion $suggestion, SuggestionService $service)
    {
        $this->authorizeAbility('manage');
        $data = $request->validate([
            'status' => ['required', Rule::in(array_keys(Suggestion::STATUSES))],
            'status_reason' => 'nullable|string|max:1000',
            'assigned_admin_id' => ['nullable', 'integer', Rule::in(StaffPermissions::suggestionStaff('manage')->pluck('id')->all())],
            'internal_priority' => ['required', Rule::in(array_keys(Suggestion::PRIORITIES))],
        ], ['status_reason.max' => 'الرد أطول من المسموح (1000 حرف)']);

        $by = Auth::user();
        $service->changeStatus($suggestion, $data['status'], $data['status_reason'] ?? null, $by);
        $service->assign($suggestion, isset($data['assigned_admin_id']) ? User::find($data['assigned_admin_id']) : null, $by);
        $service->setPriority($suggestion, $data['internal_priority'], $by);

        return back()->with('success', 'تم تحديث الاقتراح');
    }

    public function addNote(Request $request, Suggestion $suggestion)
    {
        $this->authorizeAbility('manage');
        $data = $request->validate(['body' => 'required|string|max:3000'], ['body.required' => 'اكتب الملاحظة أولًا']);
        $suggestion->notes()->create(['user_id' => Auth::id(), 'body' => $data['body']]);

        return back()->with('success', 'أُضيفت الملاحظة (داخلية، لا تظهر لصاحب الاقتراح ولا تُرسل إشعارًا)');
    }

    public function linkDuplicate(Request $request, Suggestion $suggestion, SuggestionService $service)
    {
        $this->authorizeAbility('manage');
        if ($request->boolean('unlink')) {
            $service->linkDuplicate($suggestion, null, Auth::user());

            return back()->with('success', 'أُلغي ربط التكرار');
        }

        $request->merge(['original' => strtoupper(trim((string) $request->input('original')))]);
        $data = $request->validate(['original' => 'required|string|exists:suggestions,reference_code'], [
            'original.required' => 'اكتب رقم الاقتراح الأصلي (مثل SUG-1001)',
            'original.exists' => 'لا يوجد اقتراح بهذا الرقم',
        ]);
        try {
            $service->linkDuplicate($suggestion, Suggestion::where('reference_code', $data['original'])->first(), Auth::user());
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['original' => $e->getMessage()]);
        }

        return back()->with('success', 'رُبط الاقتراح كمكرر، وبقي كلاهما كما أُرسل');
    }

    public function export(Request $request): StreamedResponse
    {
        $this->authorizeAbility('export');
        $rows = $this->filtered($request)->with(['category', 'user', 'assignee'])->latest('id');

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // UTF-8 BOM so Excel shows Arabic
            fputcsv($out, ['الرقم', 'التاريخ', 'النوع', 'العنوان', 'الشرح', 'الحالة', 'رد الفريق', 'المصدر', 'المرسل', 'البريد', 'المسؤول', 'الأولوية', 'مكرر من']);
            $rows->chunk(500, function ($chunk) use ($out) {
                foreach ($chunk as $s) {
                    fputcsv($out, array_map([self::class, 'csvSafe'], [
                        $s->reference_code, $s->created_at->format('Y-m-d H:i'), $s->categoryLabel(), $s->title, $s->description,
                        $s->statusLabel(), $s->status_reason, $s->roleLabel(), $s->senderName(), $s->user?->email ?? $s->guest_email,
                        $s->assignee?->name, Suggestion::PRIORITIES[$s->internal_priority] ?? '', $s->duplicateOf?->reference_code,
                    ]));
                }
            });
            fclose($out);
        }, 'suggestions-' . now()->format('Y-m-d') . '.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    // ── Settings: suggestion types (the page/footer texts are in «إدارة محتوى الواجهة», messages in «نصوص الإشعارات»).

    public function settings()
    {
        $this->authorizeAbility('settings');

        return view('admin.suggestions.settings', ['categories' => SuggestionCategory::withCount('suggestions')->orderBy('sort_order')->orderBy('id')->get()]);
    }

    public function saveCategory(Request $request, ?SuggestionCategory $category = null)
    {
        $this->authorizeAbility('settings');
        $data = $request->validate([
            'name' => 'required|string|max:80',
            'sort_order' => 'nullable|integer|min:0|max:1000',
            'is_active' => 'nullable|boolean',
        ], ['name.required' => 'اكتب اسم النوع', 'name.max' => 'الاسم أطول من المسموح']);
        $data['sort_order'] = $data['sort_order'] ?? 0;
        $data['is_active'] = $request->boolean('is_active');

        $category ? $category->update($data) : SuggestionCategory::create($data);

        return back()->with('success', 'تم حفظ نوع الاقتراح');
    }

    public function deleteCategory(SuggestionCategory $category)
    {
        $this->authorizeAbility('settings');
        // A used type is only hidden, so old suggestions keep their type.
        if ($category->suggestions()->exists()) {
            $category->update(['is_active' => false]);

            return back()->with('success', 'النوع مستخدم في اقتراحات سابقة، فأُخفي من النموذج بدل حذفه');
        }
        $category->delete();

        return back()->with('success', 'حُذف النوع');
    }

    private function filtered(Request $request)
    {
        return Suggestion::query()
            ->when($request->filled('q'), function ($query) use ($request) {
                $term = '%' . str_replace(['%', '_'], ['\%', '\_'], trim((string) $request->string('q'))) . '%';
                $query->where(fn ($q) => $q->where('reference_code', 'like', $term)->orWhere('title', 'like', $term));
            })
            ->when(isset(Suggestion::STATUSES[(string) $request->string('status')]), fn ($q) => $q->where('status', (string) $request->string('status')))
            ->when(ctype_digit((string) $request->string('category')), fn ($q) => $q->where('category_id', (int) (string) $request->string('category')))
            ->when(isset(Suggestion::ROLES[(string) $request->string('role')]), fn ($q) => $q->where('user_role', (string) $request->string('role')))
            ->when($this->validDate($request, 'from'), fn ($q) => $q->whereDate('created_at', '>=', (string) $request->string('from')))
            ->when($this->validDate($request, 'to'), fn ($q) => $q->whereDate('created_at', '<=', (string) $request->string('to')));
    }

    private function validDate(Request $request, string $key): bool
    {
        return (bool) preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $request->string($key));
    }

    /** A cell starting with = + - @ (also after a tab / CR) would run as a formula in Excel. */
    private static function csvSafe($value): string
    {
        $value = (string) $value;
        $first = ltrim($value, " \t\r\n");

        return $first !== '' && in_array($first[0], ['=', '+', '-', '@'], true) ? "'" . $value : $value;
    }

    private function authorizeAbility(string $ability): void
    {
        abort_unless(StaffPermissions::canSuggestions(Auth::user(), $ability), 403, 'ليست لديك صلاحية لهذا الجزء من الاقتراحات');
    }
}
