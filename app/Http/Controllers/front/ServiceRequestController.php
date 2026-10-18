<?php

namespace App\Http\Controllers\front;

use App\Http\Controllers\Controller;
use App\Models\admin\Category;
use App\Models\admin\SubCategory;
use App\Models\ServiceForm;
use App\Models\RequestOffer;
use App\Models\ServiceRequest;
use App\Models\ServiceRequestFile;
use App\Models\User;
use App\Notifications\NewInquiryNotification;
use App\Notifications\RequestUpdateNotification;
use App\Models\ServiceRequestChange;
use App\Services\ServiceRequests\RequestAdminService;
use App\Services\ServiceRequests\RequestChangeService;
use App\Services\ServiceRequests\RequestSubmission;
use Illuminate\Support\Facades\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ServiceRequestController extends Controller
{
    /** The single request page: pick a service, answer its questions, choose a path. */
    public function create(Request $request)
    {
        $categories = $this->availableServices();
        // Coming from «شخّص مشروعك»: the description written there starts the request.
        if ($request->query('from') === 'diagnose' && ! $request->session()->hasOldInput()
            && ($diagnosis = $request->session()->get(DiagnoseController::SESSION_KEY))) {
            $request->session()->now('_old_input', ['problem' => DiagnoseController::summaryText($diagnosis)]);
        }
        $selectedId = (int) old('sub_category_id', $request->query('service'));
        $form = $selectedId ? $this->publishedForm($selectedId) : null;

        return view('website.service-requests.create', [
            'categories' => $categories,
            'form' => $form,
            'client' => Auth::user(),
            // A service asked for by link that has no published form: point to «ساعدوني أختار».
            'unavailable' => $selectedId && ! $form ? SubCategory::where('status', 1)->find($selectedId) : null,
        ]);
    }

    /** Service 3 «مشروعك ما بيوقف»: the same page in «staged project» mode (homepage card 3). */
    public function createStaged(Request $request)
    {
        return $this->create($request)->with('staged', true);
    }

    /** The questions of one service, loaded when the client changes the service. */
    public function fields(SubCategory $subCategory)
    {
        $form = $this->publishedForm($subCategory->id);
        abort_unless($form, 404);

        return view('website.service-requests.partials.service-questions', compact('form'));
    }

    public function store(Request $request)
    {
        abort_unless(Auth::user()->isClient(), 403, 'إرسال الطلبات متاح لحسابات العملاء');

        $request->validate(['sub_category_id' => 'required|integer'], ['sub_category_id.required' => 'من فضلك اختر الخدمة']);
        $form = $this->publishedForm((int) $request->input('sub_category_id'));
        if (! $form) {
            return back()->withInput()->withErrors(['sub_category_id' => 'الخدمة المختارة غير متاحة حاليًا، اختر خدمة أخرى']);
        }

        $submission = new RequestSubmission($form);
        $data = $submission->validate($request);

        // A double click (or a re-sent form) must not create the request twice.
        $fingerprint = 'request-submit:' . Auth::id() . ':' . sha1(json_encode($request->except(['_token', 'attachments', 'answer_files'])));
        if (! \Illuminate\Support\Facades\Cache::add($fingerprint, true, 30)) {
            $existing = ServiceRequest::where('user_id', Auth::id())->where('problem', $data['problem'])->where('created_at', '>=', now()->subMinute())->latest('id')->first();

            return $existing
                ? redirect()->route('requests.show', $existing)->with('Success_message', 'طلبك أُرسل بالفعل مرة واحدة.')
                : back()->withInput()->withErrors('طلبك قيد الإرسال، انتظر لحظة.');
        }
        try {
            $serviceRequest = $submission->store(Auth::user(), $data, $request);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Cache::forget($fingerprint); // a failed save may be retried at once
            throw $e;
        }
        $request->session()->forget(DiagnoseController::SESSION_KEY);
        app(\App\Services\Analytics\Tracker::class)->record('request_submitted', [
            'sub_category_id' => $serviceRequest->sub_category_id,
            'meta' => ['path' => $serviceRequest->path],
        ]);
        if ($serviceRequest->isInquiry()) {
            Notification::send(User::activeAdmins()->get(), new NewInquiryNotification($serviceRequest));
        }

        $message = $serviceRequest->isInquiry()
            ? 'تم حفظ استفسارك بالكامل. سيتواصل معك فريقنا بالأسعار، ولن يُنشر طلبك للمستقلين إلا بموافقتك.'
            : 'تم نشر طلبك للمستقلين المختصين، وستصلك العروض هنا لتقارن بينها.';

        return redirect()->route('requests.show', $serviceRequest)->with('Success_message', $message)->with('request_created', true);
    }

    public function index()
    {
        $requests = ServiceRequest::where('user_id', Auth::id())->latest()->paginate(10);

        return view('website.service-requests.index', compact('requests'));
    }

    public function show(ServiceRequest $serviceRequest)
    {
        $this->authorizeView($serviceRequest);
        $serviceRequest->load(['answers', 'files', 'adminMessages']);
        if ((int) $serviceRequest->user_id === (int) Auth::id()) {
            $serviceRequest->adminMessages()->where('from_admin', true)->whereNull('read_at')->update(['read_at' => now()]);
        }
        $filesByQuestion = $serviceRequest->files->groupBy(fn ($file) => $file->question_id ?? 'attachments');

        // Offers: every offer the freelancers did not withdraw, in tabs and sorted.
        $allOffers = $serviceRequest->offers()
            ->where('status', '!=', RequestOffer::STATUS_WITHDRAWN)
            ->with(['freelancer', 'files'])
            ->withCount(['messages as unread_count' => fn ($q) => $q->whereNull('read_at')->where('sender_id', '!=', Auth::id())])
            ->get();
        $tabs = [
            'all' => $allOffers,
            'new' => $allOffers->where('status', RequestOffer::STATUS_NEW),
            'saved' => $allOffers->where('is_saved', true),
            'discussion' => $allOffers->where('status', RequestOffer::STATUS_IN_DISCUSSION),
            'selected' => $allOffers->where('status', RequestOffer::STATUS_ACCEPTED),
            'rejected' => $allOffers->where('status', RequestOffer::STATUS_REJECTED),
        ];
        $tab = array_key_exists(request('tab'), $tabs) ? request('tab') : 'all';
        $sort = in_array(request('sort'), ['best', 'price', 'fastest', 'newest'], true) ? request('sort') : 'best';
        $offers = (match ($sort) {
            'price' => $tabs[$tab]->sortBy(fn ($offer) => (float) $offer->price),
            'fastest' => $tabs[$tab]->sortBy('duration_days'),
            'newest' => $tabs[$tab]->sortByDesc(fn ($offer) => [$offer->created_at->timestamp, $offer->id]),
            default => $tabs[$tab]->sortByDesc(fn ($offer) => [$offer->matchScore($serviceRequest), -(float) $offer->price]),
        })->values();
        $newOfferIds = $tabs['new']->pluck('id')->all();

        // Opening the page counts as seeing the new offers (for the owner only).
        if ((int) $serviceRequest->user_id === (int) Auth::id()) {
            RequestOffer::whereIn('id', $newOfferIds)->update(['status' => RequestOffer::STATUS_VIEWED, 'viewed_at' => now()]);
        }

        return view('website.service-requests.show', compact('serviceRequest', 'filesByQuestion', 'offers', 'tabs', 'tab', 'sort', 'newOfferIds'));
    }

    /** The client turns a private price inquiry into a published request. */
    public function publish(ServiceRequest $serviceRequest)
    {
        abort_unless((int) $serviceRequest->user_id === (int) Auth::id(), 403);
        if (! $serviceRequest->isInquiry()) {
            return back();
        }

        $serviceRequest->publish();

        return back()->with('Success_message', 'تم نشر طلبك للمستقلين المختصين بنفس البيانات، وستصلك العروض هنا.');
    }

    /** The client writes to the site team about this request. */
    public function sendTeamMessage(Request $request, ServiceRequest $serviceRequest)
    {
        abort_unless((int) $serviceRequest->user_id === (int) Auth::id(), 403);
        $data = $request->validate(
            ['body' => 'required|string|max:3000'],
            ['body.required' => 'اكتب رسالتك أولًا', 'body.max' => 'الرسالة يجب ألا تتجاوز 3000 حرف']
        );

        $serviceRequest->adminMessages()->create(['sender_id' => Auth::id(), 'from_admin' => false, 'body' => $data['body']]);
        Notification::send(User::activeAdmins()->get(), new RequestUpdateNotification(
            \App\Support\Msg::t('admin.request_client_message', ['name' => $serviceRequest->contact_name, 'number' => $serviceRequest->number]),
            '/admin/requests/' . $serviceRequest->number . '#messages',
            'admin_alerts'
        ));

        return redirect()->to(route('requests.show', $serviceRequest) . '#team-messages')->with('Success_message', 'وصلت رسالتك لفريق نفّذها، وسنرد عليك هنا.');
    }

    /** Before an offer is chosen the client edits the whole request (client decision 2026-10-16). */
    public function edit(Request $request, ServiceRequest $serviceRequest)
    {
        if ($serviceRequest->isDirect()) {
            return redirect()->route('projects.edit', $serviceRequest);
        }
        $form = $this->editableForm($serviceRequest);
        if (! $request->session()->hasOldInput()) {
            // Pre-fill the shared form partials (they read old()) with the request as it is.
            $request->session()->now('_old_input', [
                'problem' => $serviceRequest->problem,
                'contact_name' => $serviceRequest->contact_name,
                'contact_phone' => $serviceRequest->contact_phone,
                'contact_email' => $serviceRequest->contact_email,
                'contact_method' => $serviceRequest->contact_method,
                'project_title' => $serviceRequest->project_title,
                'stopped_project' => $serviceRequest->stopped_project ? '1' : null,
                'done_before' => $serviceRequest->done_before,
                'answers' => $serviceRequest->answers->mapWithKeys(fn ($answer) => [$answer->question_id => match (true) {
                    is_array($answer->value) && array_key_exists('scale', $answer->value) => $answer->value['value'] ?? null,
                    default => $answer->value,
                }])->all(),
            ]);
        }

        return view('website.service-requests.edit', [
            'serviceRequest' => $serviceRequest,
            'form' => $form,
            'client' => Auth::user(),
            'existingFiles' => $serviceRequest->files->groupBy(fn ($file) => $file->question_id ?? 'attachments'),
            'offersCount' => $serviceRequest->offers()->whereIn('status', RequestOffer::ACTIVE_STATUSES)->count(),
        ]);
    }

    public function update(Request $request, ServiceRequest $serviceRequest)
    {
        $form = $this->editableForm($serviceRequest);
        $submission = (new RequestSubmission($form))->editing($serviceRequest);
        $data = $submission->validate($request);
        $changed = $submission->update($serviceRequest, $data, $request);

        if ($changed) {
            \App\Models\ServiceRequestEvent::record($serviceRequest, \App\Models\ServiceRequestEvent::TYPE_EDITED, ['note' => 'تغيّر: ' . implode('، ', $changed)]);
            // Freelancers who already sent an offer are told, so they can update it.
            $freelancers = User::whereIn('id', $serviceRequest->offers()->whereIn('status', RequestOffer::ACTIVE_STATUSES)->select('freelancer_id'))->get();
            Notification::send($freelancers, new RequestUpdateNotification(
                \App\Support\Msg::t('requests.edited_for_bidders', ['number' => $serviceRequest->number]),
                '/client-requests/' . $serviceRequest->number,
                'request_changes'
            ));
        }

        return redirect()->route('requests.show', $serviceRequest)
            ->with('Success_message', $changed ? 'تم حفظ تعديلات طلبك.' : 'لم يتغير شيء في طلبك.');
    }

    /** After work started: the client asks the freelancer for a change (scope, deadline, price). */
    public function proposeChange(Request $request, ServiceRequest $serviceRequest, RequestChangeService $changes)
    {
        abort_unless((int) $serviceRequest->user_id === (int) Auth::id(), 403);
        abort_if($serviceRequest->isStaged(), 403, 'تعديلات المشروع بمراحل تتم بملحق من صفحة العقد');
        $data = $request->validate([
            'description' => 'required|string|min:10|max:3000',
            'reason' => 'nullable|string|max:1000',
            'new_deadline' => 'nullable|date_format:Y-m-d|after_or_equal:today',
            'new_price' => 'nullable|numeric|min:1|max:1000000',
        ], [
            'description.required' => 'اكتب التعديل المطلوب',
            'description.min' => 'اشرح التعديل بشكل أوضح (10 أحرف على الأقل)',
            'new_deadline.after_or_equal' => 'الموعد الجديد لا يكون في الماضي',
            'new_deadline.date_format' => 'تاريخ الموعد غير صحيح',
            'new_price.min' => 'السعر الجديد يجب أن يكون 1 على الأقل',
            'new_price.numeric' => 'السعر الجديد يجب أن يكون رقمًا',
        ]);

        try {
            $changes->propose($serviceRequest, Auth::user(), $data);
        } catch (\RuntimeException $e) {
            return back()->withInput()->withErrors($e->getMessage());
        }

        return redirect()->to(route('requests.show', $serviceRequest) . '#changes')->with('Success_message', 'أُرسل طلب التعديل للمستقل، وسيصلك رده هنا.');
    }

    public function changeAction(ServiceRequest $serviceRequest, ServiceRequestChange $change, string $action, RequestChangeService $changes)
    {
        abort_unless((int) $serviceRequest->user_id === (int) Auth::id() && (int) $change->service_request_id === (int) $serviceRequest->id, 403);

        $back = route('requests.show', $serviceRequest) . '#changes';
        try {
            if ($action === 'pay') {
                return redirect()->away($changes->startCheckout($change, Auth::user()));
            }
            $action === 'cancel' ? $changes->cancel($change, Auth::user()) : $changes->payFromBalance($change, Auth::user());
        } catch (\RuntimeException $e) {
            return redirect()->to($back)->withErrors($e->getMessage());
        }

        return redirect()->to($back)->with('Success_message', $action === 'cancel' ? 'أُلغي طلب التعديل.' : 'دُفع الفرق من رصيدك واعتُمد التعديل.');
    }

    /** The client cancels a request before choosing an offer (nothing was paid). */
    public function cancel(Request $request, ServiceRequest $serviceRequest, RequestAdminService $admin)
    {
        abort_unless((int) $serviceRequest->user_id === (int) Auth::id(), 403);
        if (! in_array($serviceRequest->status, [ServiceRequest::STATUS_OPEN, ServiceRequest::STATUS_INQUIRY], true)) {
            return back()->withErrors('لا يمكن إلغاء الطلب بعد اختيار عرض. تواصل مع فريق نفّذها.');
        }
        $data = $request->validate(['note' => 'nullable|string|max:1000']);

        $admin->close($serviceRequest, 'client_cancelled', $data['note'] ?? null, Auth::user());

        return back()->with('Success_message', 'تم إلغاء طلبك.');
    }

    public function file(ServiceRequest $serviceRequest, ServiceRequestFile $file)
    {
        $this->authorizeView($serviceRequest);
        abort_unless((int) $file->service_request_id === (int) $serviceRequest->id, 404);
        abort_unless(Storage::disk(ServiceRequestFile::DISK)->exists($file->path), 404);

        return Storage::disk(ServiceRequestFile::DISK)->download($file->path, $file->original_name);
    }

    /** The request's own form (questions as they were) while it is still editable by its owner. */
    private function editableForm(ServiceRequest $serviceRequest): ServiceForm
    {
        abort_unless((int) $serviceRequest->user_id === (int) Auth::id(), 403);
        abort_unless(in_array($serviceRequest->status, [ServiceRequest::STATUS_OPEN, ServiceRequest::STATUS_INQUIRY], true), 403,
            'بعد اختيار عرض، اطلب التعديل من صفحة الطلب ليوافق عليه المستقل.');
        $form = $serviceRequest->form()->with(['subCategory.category', 'sections.questions'])->first();
        abort_unless($form, 404);

        return $form;
    }

    private function authorizeView(ServiceRequest $serviceRequest): void
    {
        $user = Auth::user();
        abort_unless((int) $serviceRequest->user_id === (int) $user->id || ($user->account_type === User::TYPE_ADMIN && (int) $user->status === 1), 403);
    }

    /** Active categories with their services that have a published form. */
    private function availableServices()
    {
        $publishedIds = ServiceForm::published()->pluck('sub_category_id');

        return Category::where('status', 1)
            ->with(['subCategories' => fn ($query) => $query->where('status', 1)->whereIn('id', $publishedIds)->orderBy('name')])
            ->orderBy('name')
            ->get()
            ->filter(fn ($category) => $category->subCategories->isNotEmpty())
            ->values();
    }

    private function publishedForm(int $subCategoryId): ?ServiceForm
    {
        return ServiceForm::published()
            ->where('sub_category_id', $subCategoryId)
            ->whereHas('subCategory', fn ($query) => $query->where('status', 1)
                ->whereHas('category', fn ($category) => $category->where('status', 1)))
            ->with(['subCategory.category', 'sections.questions'])
            ->first();
    }
}
