<?php

namespace App\Http\Controllers\front;

use App\Http\Controllers\Controller;
use App\Models\admin\Category;
use App\Models\admin\SubCategory;
use App\Models\RequestOffer;
use App\Models\ServiceFormQuestion as Q;
use App\Models\ServiceRequest;
use App\Models\ServiceRequestEvent;
use App\Models\ServiceRequestFile;
use App\Models\User;
use App\Notifications\RequestUpdateNotification;
use App\Services\Analytics\Tracker;
use App\Services\ServiceRequests\RequestSubmission;
use App\Support\Money;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

/**
 * «انشر مشروعك» (client file «المربعات الخمسة»): the client writes the project himself
 * (title, description, service, budget, deadline, attachments) and it is published at
 * once to the freelancers of that service — the same ServiceRequest as the guided
 * questions, with source = direct. A visitor may write it first: the text is kept in a
 * draft (session, and the browser) and restored after logging in.
 */
class ProjectPostController extends Controller
{
    public const DRAFT_KEY = 'project_draft';

    private const DRAFT_FIELDS = ['title', 'description', 'sub_category_id', 'budget_min', 'budget_max', 'deadline'];

    public function create(Request $request)
    {
        $user = Auth::user();
        $draft = (array) $request->session()->get(self::DRAFT_KEY, []);
        $restored = $draft ? 'login' : null;
        if (! $draft && ($diagnosis = $request->session()->get(DiagnoseController::SESSION_KEY))) {
            $draft = array_filter(['description' => DiagnoseController::summaryText($diagnosis), 'sub_category_id' => $diagnosis['service'] ?? null]);
            $restored = $draft ? 'diagnosis' : null;
        }
        if ($draft && ! $request->session()->hasOldInput()) {
            $request->session()->now('_old_input', $draft);
        }

        return view('website.projects.create', [
            'groups' => $this->services(),
            'user' => $user,
            'canPost' => ! $user || $user->isClient(),
            'restored' => $restored,
            'selected' => $request->integer('service') ?: null,
        ]);
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        if (! $user) {
            // A visitor: keep what he wrote and ask him to log in (files are added after that).
            $data = $request->validate($this->rules(false), $this->messages());
            $request->session()->put(self::DRAFT_KEY, array_intersect_key($data, array_flip(self::DRAFT_FIELDS)));
            $request->session()->put('url.intended', route('projects.new'));

            return redirect()->route('user_login')->with('Success_message', 'حفظنا مشروعك كمسودة. سجّل دخولك أو أنشئ حساب عميل لتنشره، وستجده كما كتبته.');
        }
        if (! $user->account_type) {
            $request->session()->put(self::DRAFT_KEY, array_intersect_key($request->only(self::DRAFT_FIELDS), array_flip(self::DRAFT_FIELDS)));

            return redirect()->route('account.type');
        }
        abort_unless($user->isClient(), 403, 'نشر المشاريع متاح لحسابات العملاء');

        $data = $request->validate($this->rules(true, $user), $this->messages());

        // A double click (or a re-sent form) must not publish the project twice.
        $fingerprint = 'project-post:' . $user->id . ':' . sha1(json_encode($request->except(['_token', 'attachments'])));
        if (! Cache::add($fingerprint, true, 30)) {
            $existing = ServiceRequest::where('user_id', $user->id)->where('source', ServiceRequest::SOURCE_DIRECT)
                ->where('project_title', $data['title'])->where('created_at', '>=', now()->subMinute())->latest('id')->first();

            return $existing
                ? redirect()->route('requests.show', $existing)->with('Success_message', 'مشروعك نُشر بالفعل مرة واحدة.')
                : back()->withInput()->withErrors('مشروعك قيد النشر، انتظر لحظة.');
        }

        $service = SubCategory::with('category')->findOrFail($data['sub_category_id']);
        $paths = [];
        try {
            $project = DB::transaction(function () use ($user, $data, $service, $request, &$paths) {
                $project = ServiceRequest::create([
                    'user_id' => $user->id,
                    'source' => ServiceRequest::SOURCE_DIRECT,
                    'category_id' => $service->category?->id,
                    'sub_category_id' => $service->id,
                    'category_name' => $service->category?->name,
                    'service_name' => $service->name,
                    'path' => ServiceRequest::PATH_EXECUTE,
                    'kind' => ServiceRequest::KIND_STANDARD,
                    'project_title' => $data['title'],
                    'status' => ServiceRequest::STATUS_OPEN,
                    'problem' => $data['description'],
                    'contact_name' => $user->name,
                    'contact_phone' => $data['contact_phone'] ?? $user->phone,
                    'contact_email' => $user->email,
                    'published_at' => now(),
                ] + $this->filters($data));
                $project->update(['number' => 'REQ-' . (1000 + $project->id)]);
                $this->storeDetails($project, $data);
                foreach ((array) $request->file('attachments', []) as $file) {
                    $paths[] = $this->storeFile($project, $file);
                }

                return $project;
            });
        } catch (\Throwable $e) {
            Storage::disk(ServiceRequestFile::DISK)->delete($paths);
            Cache::forget($fingerprint);
            throw $e;
        }
        if (empty($user->phone) && ! empty($data['contact_phone'])) {
            $user->update(['phone' => $data['contact_phone']]);
        }
        $request->session()->forget([self::DRAFT_KEY, DiagnoseController::SESSION_KEY]);
        app(Tracker::class)->record('project_published', ['sub_category_id' => $project->sub_category_id, 'meta' => ['request' => $project->number]]);

        return redirect()->route('requests.show', $project)
            ->with('Success_message', 'تم نشر مشروعك ' . $project->number . ' للمستقلين المختصين في «' . $project->service_name . '». ستصلك العروض هنا لتقارن بينها.')
            ->with('request_created', true);
    }

    /** The owner edits a direct project while it is open (after work starts: a change request on the request page). */
    public function edit(Request $request, ServiceRequest $serviceRequest)
    {
        $this->editable($serviceRequest);
        if (! $request->session()->hasOldInput()) {
            $request->session()->now('_old_input', [
                'title' => $serviceRequest->project_title,
                'description' => $serviceRequest->problem,
                'sub_category_id' => $serviceRequest->sub_category_id,
                'budget_min' => $serviceRequest->budget_min !== null ? (float) $serviceRequest->budget_min : null,
                'budget_max' => $serviceRequest->budget_max !== null ? (float) $serviceRequest->budget_max : null,
                'deadline' => $serviceRequest->deadline?->format('Y-m-d'),
            ]);
        }

        return view('website.projects.create', [
            'groups' => $this->services(),
            'user' => Auth::user(),
            'canPost' => true,
            'restored' => false,
            'selected' => null,
            'project' => $serviceRequest->load('files'),
        ]);
    }

    public function update(Request $request, ServiceRequest $serviceRequest)
    {
        $this->editable($serviceRequest);
        $kept = $serviceRequest->files()->whereNotIn('id', (array) $request->input('remove_files', []))->count();
        $rules = $this->rules(true, Auth::user(), $kept);
        $rules['contact_phone'][0] = 'nullable'; // not on the edit page; the request keeps its contact data
        $data = $request->validate($rules + ['remove_files' => 'nullable|array', 'remove_files.*' => 'integer'], $this->messages());
        $service = SubCategory::with('category')->findOrFail($data['sub_category_id']);

        $before = [$serviceRequest->project_title, $serviceRequest->problem, $serviceRequest->sub_category_id, (float) $serviceRequest->budget_min, (float) $serviceRequest->budget_max, $serviceRequest->deadline?->format('Y-m-d')];
        $removed = [];
        DB::transaction(function () use ($serviceRequest, $data, $service, $request, &$removed) {
            $serviceRequest->update([
                'project_title' => $data['title'],
                'problem' => $data['description'],
                'category_id' => $service->category?->id,
                'sub_category_id' => $service->id,
                'category_name' => $service->category?->name,
                'service_name' => $service->name,
                'edited_at' => now(),
            ] + $this->filters($data));
            $serviceRequest->answers()->delete();
            $this->storeDetails($serviceRequest, $data);
            foreach ($serviceRequest->files()->whereIn('id', (array) ($data['remove_files'] ?? []))->get() as $file) {
                $removed[] = $file->path;
                $file->delete();
            }
            foreach ((array) $request->file('attachments', []) as $file) {
                $this->storeFile($serviceRequest, $file);
            }
        });
        Storage::disk(ServiceRequestFile::DISK)->delete($removed);

        $after = [$data['title'], $data['description'], (int) $data['sub_category_id'], (float) ($data['budget_min'] ?? 0), (float) ($data['budget_max'] ?? 0), $data['deadline'] ?? null];
        $changed = $before != $after || $removed || $request->hasFile('attachments');
        if ($changed) {
            ServiceRequestEvent::record($serviceRequest, ServiceRequestEvent::TYPE_EDITED, ['note' => 'عدّل العميل المشروع المنشور مباشرة']);
            $freelancers = User::whereIn('id', $serviceRequest->offers()->whereIn('status', RequestOffer::ACTIVE_STATUSES)->select('freelancer_id'))->get();
            Notification::send($freelancers, new RequestUpdateNotification(
                \App\Support\Msg::t('requests.project_edited_for_bidders', ['number' => $serviceRequest->number]),
                '/client-requests/' . $serviceRequest->number,
                'request_changes'
            ));
        }

        return redirect()->route('requests.show', $serviceRequest)->with('Success_message', $changed ? 'تم حفظ تعديلات مشروعك.' : 'لم يتغير شيء في مشروعك.');
    }

    private function editable(ServiceRequest $serviceRequest): void
    {
        abort_unless((int) $serviceRequest->user_id === (int) Auth::id() && $serviceRequest->isDirect(), 403);
        abort_unless(in_array($serviceRequest->status, [ServiceRequest::STATUS_OPEN, ServiceRequest::STATUS_INQUIRY], true), 403,
            'بعد اختيار عرض، اطلب التعديل من صفحة الطلب ليوافق عليه المستقل.');
    }

    private function rules(bool $full, ?User $user = null, int $keptFiles = 0): array
    {
        $rules = [
            'title' => 'required|string|min:5|max:150',
            'description' => 'required|string|min:30|max:5000',
            // A visible service in a visible group.
            'sub_category_id' => ['required', 'integer', Rule::exists('sub_categories', 'id')->where('status', 1)
                ->where(fn ($q) => $q->whereIn('parent_id', Category::where('status', 1)->select('id')))],
            'budget_min' => 'nullable|numeric|min:1|max:10000000',
            'budget_max' => 'nullable|numeric|min:1|max:10000000|gte:budget_min',
            'deadline' => 'nullable|date_format:Y-m-d|after:today',
        ];
        if ($full) {
            $rules += [
                'contact_phone' => [empty($user?->phone) ? 'required' : 'nullable', 'string', 'max:30', 'regex:/^\+?[0-9\s\-()]{6,30}$/'],
                'attachments' => 'nullable|array|max:' . max(0, RequestSubmission::ATTACHMENTS_MAX - $keptFiles),
                'attachments.*' => 'file|max:' . (RequestSubmission::ATTACHMENT_MAX_MB * 1024) . '|mimes:' . implode(',', RequestSubmission::ATTACHMENT_EXTENSIONS),
            ];
        }

        return $rules;
    }

    private function messages(): array
    {
        return [
            'title.required' => 'اكتب عنوانًا لمشروعك',
            'title.min' => 'العنوان قصير جدًا',
            'description.required' => 'اكتب وصف المشروع',
            'description.min' => 'اشرح مشروعك بتفصيل أكثر (30 حرفًا على الأقل) ليفهمه المستقلون',
            'sub_category_id.required' => 'اختر مجال المشروع',
            'sub_category_id.exists' => 'المجال المختار غير متاح',
            'budget_max.gte' => 'أقصى الميزانية يجب ألا يقل عن أدناها',
            'budget_min.min' => 'الميزانية رقم أكبر من صفر',
            'budget_max.min' => 'الميزانية رقم أكبر من صفر',
            'deadline.after' => 'الموعد المطلوب يكون بعد اليوم',
            'deadline.date_format' => 'تاريخ غير صحيح',
            'contact_phone.required' => 'اكتب رقم تواصل (لا يظهر للمستقلين)',
            'contact_phone.regex' => 'رقم التواصل غير صحيح',
            'attachments.max' => 'الحد الأقصى ' . RequestSubmission::ATTACHMENTS_MAX . ' مرفقات',
            'attachments.*.max' => 'حجم المرفق يجب ألا يتجاوز ' . RequestSubmission::ATTACHMENT_MAX_MB . ' ميجابايت',
            'attachments.*.mimes' => 'نوع المرفق غير مسموح',
        ];
    }

    /** The board's filter columns. */
    private function filters(array $data): array
    {
        return [
            'budget_min' => $data['budget_min'] ?? null,
            'budget_max' => $data['budget_max'] ?? null,
            'deadline' => $data['deadline'] ?? null,
        ];
    }

    /** Budget and deadline as answer rows, so every request page shows them the same way. */
    private function storeDetails(ServiceRequest $project, array $data): void
    {
        $rows = [];
        $min = $data['budget_min'] ?? null;
        $max = $data['budget_max'] ?? null;
        if ($min !== null || $max !== null) {
            $rows['الميزانية'] = match (true) {
                $min !== null && $max !== null => 'من ' . Money::format($min) . ' إلى ' . Money::format($max),
                $min !== null => 'من ' . Money::format($min),
                default => 'حتى ' . Money::format($max),
            };
        }
        if (! empty($data['deadline'])) {
            $rows['الموعد المطلوب'] = $data['deadline'];
        }
        $order = 0;
        foreach ($rows as $label => $value) {
            $project->answers()->create(['question_id' => null, 'section_title' => 'تفاصيل المشروع', 'label' => $label, 'type' => Q::TYPE_SHORT_TEXT, 'value' => $value, 'sort_order' => ++$order]);
        }
    }

    private function storeFile(ServiceRequest $project, $file): string
    {
        $path = $file->store('service-requests/' . $project->id, ServiceRequestFile::DISK);
        $project->files()->create([
            'question_id' => null,
            'original_name' => mb_substr($file->getClientOriginalName(), 0, 255),
            'path' => $path,
            'mime' => $file->getClientMimeType(),
            'size' => $file->getSize(),
        ]);

        return $path;
    }

    /** Every active service in its group (a direct project does not need a request form). */
    private function services()
    {
        return Category::where('status', 1)
            ->with(['subCategories' => fn ($q) => $q->where('status', 1)->orderBy('sort_order')->orderBy('name')])
            ->orderBy('sort_order')->orderBy('name')
            ->get()
            ->filter(fn ($group) => $group->subCategories->isNotEmpty());
    }
}
