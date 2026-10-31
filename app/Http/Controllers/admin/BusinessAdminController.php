<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\BusinessCompany;
use App\Models\BusinessDelivery;
use App\Models\BusinessProject;
use App\Models\BusinessRequest;
use App\Models\BusinessTask;
use App\Models\User;
use App\Services\Business\BusinessService;
use Database\Seeders\BusinessFormSeeder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

/**
 * «نفّذها للأعمال» in the admin (client file «قسم الشركات», phase 1): the unified requests inbox (both paths),
 * quote, payment, linking a visitor to an account, converting to a project; projects with the task board,
 * deliveries (versions) and the log. Read-only roles are stopped by EnsureUserIsAdmin (non-GET requests).
 */
class BusinessAdminController extends Controller
{
    /** Log actions shown to people (admin log and the client's history). */
    public const ACTIONS = [
        'created' => 'استُلم الطلب', 'status' => 'تغيّرت الحالة', 'assigned' => 'إسناد', 'note' => 'ملاحظة داخلية',
        'quote' => 'أُرسل عرض السعر', 'quote_accepted' => 'قُبل عرض السعر', 'quote_rejected' => 'رُفض عرض السعر', 'paid' => 'سُجّل الدفع',
        'account' => 'ربط الحساب', 'converted' => 'تحوّل لمشروع', 'task' => 'المهام', 'delivery' => 'أُرسلت نسخة للاعتماد',
        'approved' => 'اعتُمدت نسخة', 'changes' => 'طُلب تعديل', 'project' => 'تحديث المشروع',
    ];

    public static function actionLabel(string $action): string
    {
        return self::ACTIONS[$action] ?? $action;
    }

    public function __construct(private BusinessService $business)
    {
    }

    // ── Requests inbox ─────────────────────────────────────────────────

    public function requests(Request $request)
    {
        $q = is_string($request->query('q')) ? trim($request->query('q')) : '';
        $filters = ['q' => $q] + $request->only(['status', 'source', 'size', 'assignee', 'payment']);
        $items = BusinessRequest::with(['assignee:id,name', 'user:id,name'])
            ->when($q !== '', function ($query) use ($q) {
                $term = '%' . str_replace(['%', '_'], ['\%', '\_'], $q) . '%';
                $query->where(fn ($w) => $w->where('number', 'like', $term)->orWhere('company_name', 'like', $term)->orWhere('phone', 'like', $term)
                    ->orWhere('email', 'like', $term)->orWhere('contact_name', 'like', $term)->orWhere('details', 'like', $term));
            })
            ->when(isset(BusinessRequest::STATUSES[(string) $request->query('status')]), fn ($w) => $w->where('status', $request->query('status')))
            ->when(isset(BusinessRequest::SOURCES[(string) $request->query('source')]), fn ($w) => $w->where('source', $request->query('source')))
            ->when(isset(BusinessCompany::SIZES[(string) $request->query('size')]), fn ($w) => $w->where('company_size', $request->query('size')))
            ->when($request->query('assignee') === 'none', fn ($w) => $w->whereNull('assigned_to'))
            ->when(ctype_digit((string) $request->query('assignee')), fn ($w) => $w->where('assigned_to', (int) $request->query('assignee')))
            ->when(in_array($request->query('payment'), ['paid', 'unpaid'], true), fn ($w) => $w->where('payment_status', $request->query('payment')))
            ->latest('id')->paginate(25)->withQueryString();

        return view('admin.business.requests', [
            'items' => $items, 'filters' => $filters,
            'counts' => BusinessRequest::selectRaw('status, COUNT(*) as total')->groupBy('status')->pluck('total', 'status'),
            'staff' => $this->business->teamMembers(),
        ]);
    }

    public function showRequest(BusinessRequest $businessRequest)
    {
        $businessRequest->load(['user', 'assignee', 'files', 'project', 'events.user:id,name']);

        return view('admin.business.request', [
            'item' => $businessRequest,
            'staff' => $this->business->teamMembers(),
            'company' => $businessRequest->user_id ? BusinessCompany::where('user_id', $businessRequest->user_id)->first() : null,
        ]);
    }

    public function updateRequest(Request $request, BusinessRequest $businessRequest)
    {
        // The select offers the current status + the manual ones; quote / acceptance / conversion have their own actions.
        $data = $request->validate([
            'status' => ['required', Rule::in(array_unique(array_merge(BusinessRequest::MANUAL_STATUSES, [$businessRequest->status])))],
            'status_note' => 'nullable|string|max:1000',
            'assigned_to' => ['nullable', Rule::in($this->business->teamMembers()->pluck('id')->all())],
        ]);
        if ($businessRequest->status !== BusinessRequest::STATUS_CONVERTED) {
            $this->business->setStatus($businessRequest, $data['status'], $data['status_note'] ?? null, Auth::user());
        }
        $this->business->assign($businessRequest, isset($data['assigned_to']) ? User::find($data['assigned_to']) : null, Auth::user());

        return back()->with('success', 'تم تحديث الطلب');
    }

    public function note(Request $request, BusinessRequest $businessRequest)
    {
        $data = $request->validate(['body' => 'required|string|max:3000'], ['body.required' => 'اكتب الملاحظة أولًا']);
        $this->business->note($businessRequest, $data['body'], Auth::user());

        return back()->with('success', 'أُضيفت الملاحظة (داخلية، لا يراها العميل)');
    }

    public function quote(Request $request, BusinessRequest $businessRequest)
    {
        abort_if($businessRequest->status === BusinessRequest::STATUS_CONVERTED, 422, 'الطلب تحوّل لمشروع بالفعل');
        $data = $request->validate([
            'quote_amount' => 'required|numeric|min:1|max:100000000',
            'quote_scope' => 'required|string|max:5000',
            'quote_due_at' => 'nullable|date_format:Y-m-d|after_or_equal:today',
        ], ['quote_amount.required' => 'اكتب مبلغ العرض', 'quote_scope.required' => 'اكتب نطاق العمل والمخرجات', 'quote_due_at.after_or_equal' => 'تاريخ صلاحية العرض في الماضي']);
        $this->business->sendQuote($businessRequest, (float) $data['quote_amount'], $data['quote_scope'], $data['quote_due_at'] ?? null, Auth::user());

        return back()->with('success', $businessRequest->user_id ? 'أُرسل عرض السعر للعميل في حسابه' : 'سُجّل عرض السعر؛ العميل زائر بلا حساب، أبلغه بالهاتف أو اربطه بحساب');
    }

    public function decideForClient(Request $request, BusinessRequest $businessRequest)
    {
        $data = $request->validate(['decision' => ['required', Rule::in(['accept', 'reject'])], 'note' => 'nullable|string|max:1000']);
        try {
            $this->business->decideQuote($businessRequest, $data['decision'] === 'accept', $data['note'] ?? null, Auth::user());
        } catch (\RuntimeException $e) {
            return back()->withErrors(['decision' => $e->getMessage()]);
        }

        return back()->with('success', 'سُجّل قرار العميل');
    }

    public function payment(Request $request, BusinessRequest $businessRequest)
    {
        $data = $request->validate([
            'payment_method' => ['required', Rule::in(array_diff(array_keys(BusinessRequest::PAYMENT_METHODS), ['online']))],
            'payment_reference' => 'nullable|string|max:120',
        ], ['payment_method.required' => 'اختر طريقة الدفع']);
        abort_unless($businessRequest->hasQuote(), 422, 'لا يوجد عرض سعر لهذا الطلب');
        $this->business->markPaid($businessRequest, $data['payment_method'], $data['payment_reference'] ?? null, Auth::user());

        return back()->with('success', 'سُجّل الدفع');
    }

    public function linkAccount(Request $request, BusinessRequest $businessRequest)
    {
        abort_if($businessRequest->user_id, 422, 'الطلب مربوط بحساب بالفعل');
        $data = $request->validate(['email' => 'required|email:rfc|max:150', 'name' => 'nullable|string|max:100'],
            ['email.required' => 'اكتب بريد العميل', 'email.email' => 'البريد غير صحيح']);
        try {
            $user = $this->business->linkAccount($businessRequest, $data['email'], $data['name'] ?? null, Auth::user());
        } catch (\RuntimeException $e) {
            return back()->withErrors(['email' => $e->getMessage()]);
        }

        return back()->with('success', 'رُبط الطلب بحساب ' . $user->email . ($user->wasRecentlyCreated ? ' (أُرسل له رابط ضبط كلمة المرور)' : ''));
    }

    public function convert(Request $request, BusinessRequest $businessRequest)
    {
        $data = $request->validate([
            'title' => 'required|string|max:150',
            'manager_id' => ['nullable', Rule::in($this->business->teamMembers()->pluck('id')->all())],
            'due_at' => 'nullable|date_format:Y-m-d',
        ], ['title.required' => 'اكتب عنوان المشروع']);
        try {
            $project = $this->business->convert($businessRequest, $data['title'], isset($data['manager_id']) ? User::find($data['manager_id']) : null, $data['due_at'] ?? null, Auth::user());
        } catch (\RuntimeException $e) {
            return back()->withErrors(['title' => $e->getMessage()]);
        }

        return redirect()->route('admin.business.projects.show', $project)->with('success', 'تحوّل الطلب إلى المشروع ' . $project->number);
    }

    // ── Projects ───────────────────────────────────────────────────────

    public function projects(Request $request)
    {
        $status = isset(BusinessProject::STATUSES[(string) $request->query('status')]) ? (string) $request->query('status') : BusinessProject::STATUS_ACTIVE;
        $projects = BusinessProject::where('status', $status)->with(['user:id,name', 'manager:id,name', 'tasks:id,business_project_id,status,due_at'])
            ->withCount(['deliveries as pending_count' => fn ($q) => $q->where('status', BusinessDelivery::STATUS_PENDING)])
            ->latest('id')->paginate(25)->withQueryString();

        return view('admin.business.projects', [
            'projects' => $projects, 'status' => $status,
            'counts' => BusinessProject::selectRaw('status, COUNT(*) as total')->groupBy('status')->pluck('total', 'status'),
        ]);
    }

    public function showProject(BusinessProject $businessProject)
    {
        $businessProject->load(['user', 'manager', 'request', 'tasks.assignee:id,name', 'deliveries.files', 'deliveries.task:id,title', 'events.user:id,name']);

        return view('admin.business.project', [
            'project' => $businessProject,
            'staff' => $this->business->teamMembers(),
            'company' => $businessProject->user_id ? BusinessCompany::where('user_id', $businessProject->user_id)->first() : null,
        ]);
    }

    public function updateProject(Request $request, BusinessProject $businessProject)
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(array_keys(BusinessProject::STATUSES))],
            'manager_id' => ['nullable', Rule::in($this->business->teamMembers()->pluck('id')->all())],
            'due_at' => 'nullable|date_format:Y-m-d',
        ]);
        if ((int) $businessProject->manager_id !== (int) ($data['manager_id'] ?? 0) || (string) $businessProject->due_at?->format('Y-m-d') !== (string) ($data['due_at'] ?? '')) {
            $businessProject->update(['manager_id' => $data['manager_id'] ?? null, 'due_at' => $data['due_at'] ?? null]);
            $this->business->log($businessProject, 'project', 'المسؤول: ' . ($businessProject->fresh()->manager?->name ?? '—') . ' · التسليم: ' . ($data['due_at'] ?? '—'), Auth::user());
        }
        $this->business->setProjectStatus($businessProject, $data['status'], Auth::user());

        return back()->with('success', 'تم تحديث المشروع');
    }

    public function projectNote(Request $request, BusinessProject $businessProject)
    {
        $data = $request->validate(['body' => 'required|string|max:3000'], ['body.required' => 'اكتب الملاحظة أولًا']);
        $this->business->note($businessProject, $data['body'], Auth::user());

        return back()->with('success', 'أُضيفت الملاحظة (داخلية، لا يراها العميل)');
    }

    public function saveTask(Request $request, BusinessProject $businessProject, ?BusinessTask $task = null)
    {
        abort_if($task && $task->business_project_id !== $businessProject->id, 404);
        $data = $request->validate([
            'title' => 'required|string|max:150',
            'description' => 'nullable|string|max:3000',
            'assignee_id' => ['nullable', Rule::in($this->business->teamMembers()->pluck('id')->all())],
            'status' => ['required', Rule::in(array_keys(BusinessTask::STATUSES))],
            'priority' => ['required', Rule::in(array_keys(BusinessTask::PRIORITIES))],
            'due_at' => 'nullable|date_format:Y-m-d',
        ], ['title.required' => 'اكتب عنوان المهمة']);
        $this->business->saveTask($businessProject, $data, Auth::user(), $task);

        return redirect(route('admin.business.projects.show', $businessProject) . '#tasks')->with('success', $task ? 'عُدّلت المهمة' : 'أُضيفت المهمة');
    }

    public function moveTask(Request $request, BusinessProject $businessProject, BusinessTask $task)
    {
        abort_if($task->business_project_id !== $businessProject->id, 404);
        $data = $request->validate(['status' => ['required', Rule::in(array_keys(BusinessTask::STATUSES))]]);
        $this->business->saveTask($businessProject, ['status' => $data['status']], Auth::user(), $task);

        return redirect(route('admin.business.projects.show', $businessProject) . '#tasks');
    }

    public function deleteTask(BusinessProject $businessProject, BusinessTask $task)
    {
        abort_if($task->business_project_id !== $businessProject->id, 404);
        $this->business->deleteTask($task, Auth::user());

        return redirect(route('admin.business.projects.show', $businessProject) . '#tasks')->with('success', 'حُذفت المهمة');
    }

    public function deliver(Request $request, BusinessProject $businessProject)
    {
        $data = $request->validate([
            'previous_id' => ['nullable', 'integer', Rule::exists('business_deliveries', 'id')->where('business_project_id', $businessProject->id)],
            'title' => 'required_without:previous_id|nullable|string|max:150',
            'business_task_id' => ['nullable', 'integer', Rule::exists('business_tasks', 'id')->where('business_project_id', $businessProject->id)],
            'note' => 'nullable|string|max:5000',
            'links' => 'nullable|string|max:3000',
            'files' => 'nullable|array|max:10',
            'files.*' => 'file|max:51200|mimes:jpg,jpeg,png,webp,gif,pdf,doc,docx,ppt,pptx,xls,xlsx,zip,rar,mp3,wav,mp4,mov',
        ], [
            'title.required_without' => 'اكتب اسم المخرج (مثل: ريلز رقم 4)',
            'files.max' => 'الحد الأقصى 10 ملفات؛ للفيديوهات الكبيرة ضع رابطًا',
            'files.*.max' => 'حجم الملف يجب ألا يتجاوز 50 ميجابايت؛ للأكبر ضع رابطًا',
            'files.*.mimes' => 'نوع الملف غير مسموح',
        ]);
        $links = collect(preg_split('/\s+/', (string) ($data['links'] ?? '')))->filter(fn ($l) => filter_var($l, FILTER_VALIDATE_URL) && preg_match('#^https?://#i', $l))->values()->all();
        if (! $links && ! $request->hasFile('files') && ! filled($data['note'] ?? null)) {
            return back()->withInput()->withErrors(['note' => 'أرفق ملفًا أو رابطًا أو اكتب ملاحظة للنسخة']);
        }
        $previous = isset($data['previous_id']) ? BusinessDelivery::find($data['previous_id']) : null;
        $this->business->deliver($businessProject, ['title' => $data['title'] ?? null, 'business_task_id' => $data['business_task_id'] ?? null,
            'note' => $data['note'] ?? null, 'links' => $links], (array) $request->file('files', []), Auth::user(), $previous);

        return redirect(route('admin.business.projects.show', $businessProject) . '#deliveries')->with('success', 'أُرسلت النسخة للعميل للاعتماد');
    }

    /** Settings: where each part of the companies section is edited (form builder, texts, notifications). */
    public function settings()
    {
        $service = BusinessFormSeeder::service();
        $form = $service ? \App\Models\ServiceForm::main()->where('sub_category_id', $service->id)->withCount('questions')->first() : null;

        return view('admin.business.settings', compact('service', 'form'));
    }
}
