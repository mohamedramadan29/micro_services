<?php

namespace App\Http\Controllers\front;

use App\Http\Controllers\Controller;
use App\Http\Traits\Message_Trait;
use App\Models\BusinessCompany;
use App\Models\BusinessDelivery;
use App\Models\BusinessDeliveryFile;
use App\Models\BusinessProject;
use App\Models\BusinessRequest;
use App\Models\BusinessRequestFile;
use App\Models\OnlinePayment;
use App\Models\ServiceForm;
use App\Services\Business\BusinessService;
use App\Services\Payments\OnlinePaymentService;
use App\Services\ServiceRequests\RequestSubmission;
use App\Support\SiteContent;
use Database\Seeders\BusinessFormSeeder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * «نفّذها للأعمال | للشركات» (client file «قسم الشركات», phase 1): the public page with its two paths — the free
 * request (long text + phone, no account) and the guided form (a form of «نماذج الخدمات», one step per section,
 * needs a client account) — and the company's area: its requests (quote, payment) and projects (approvals).
 */
class BusinessController extends Controller
{
    use Message_Trait;

    /** Saved requests per hour: per account, or per browser session for a visitor (failed attempts never count). */
    public const SENT_PER_HOUR = 5;

    public function __construct(private BusinessService $business)
    {
    }

    public function index()
    {
        return view('website.business.index', [
            'submissionKey' => (string) Str::uuid(),
            'freeMax' => $this->freeMax(),
            'guidedReady' => (bool) $this->guidedForm(),
        ]);
    }

    public function storeFree(Request $request)
    {
        if (filled($request->input('nf_extra'))) { // honeypot
            return back()->withInput()->withErrors(['details' => 'تعذّر إرسال الطلب، حاول مرة أخرى.']);
        }
        $request->merge(['phone' => \App\Support\Phone::normalize($request->input('phone'))]);
        $data = $request->validate([
            'submission_key' => 'required|uuid',
            'details' => 'required|string|min:20|max:' . $this->freeMax(),
            'phone' => ['required', 'string', 'max:30', \App\Support\Phone::RULE],
            'company_name' => 'nullable|string|max:150',
            'email' => 'nullable|email:rfc|max:150',
            'consent' => 'accepted',
            'attachments' => 'nullable|array|max:3',
            'attachments.*' => 'file|max:20480|mimes:jpg,jpeg,png,webp,pdf,doc,docx,ppt,pptx,xls,xlsx,zip,rar,mp3,mp4',
        ], [
            'submission_key.*' => 'انتهت صلاحية الصفحة، أعد تحميلها ثم أرسل (ما كتبته محفوظ).',
            'details.required' => 'اكتب طلبك أولًا',
            'details.min' => 'اكتب تفاصيل أكثر قليلًا (20 حرفًا على الأقل)',
            'details.max' => 'الطلب أطول من المسموح (' . $this->freeMax() . ' حرف)',
            'phone.required' => 'اكتب رقم الهاتف أو الواتساب',
            'phone.regex' => \App\Support\Phone::MESSAGE,
            'email.email' => 'البريد الإلكتروني غير صحيح',
            'consent.accepted' => 'وافق على استخدام بياناتك للتواصل بشأن طلبك',
            'attachments.max' => 'الحد الأقصى 3 مرفقات (للملفات الكبيرة ضع رابطًا في الطلب)',
            'attachments.*.max' => 'حجم المرفق يجب ألا يتجاوز 20 ميجابايت',
            'attachments.*.mimes' => 'نوع المرفق غير مسموح',
        ]);

        return $this->save($request, [
            'source' => BusinessRequest::SOURCE_FREE, 'submission_key' => $data['submission_key'], 'details' => trim($data['details']),
            'phone' => $data['phone'], 'company_name' => $data['company_name'] ?? null, 'email' => $data['email'] ?? null,
        ], array_map(fn ($file) => [null, $file], (array) $request->file('attachments', [])));
    }

    public function guided()
    {
        $form = $this->guidedForm();
        abort_unless($form, 404);
        $user = Auth::user();

        return view('website.business.guided', [
            'form' => $form,
            'company' => BusinessCompany::where('user_id', $user->id)->first(),
            'submissionKey' => (string) Str::uuid(),
            'canSend' => $user->isClient(),
        ]);
    }

    public function storeGuided(Request $request)
    {
        abort_unless(Auth::user()->isClient(), 403, 'طلبات الشركات لحسابات العملاء');
        $form = $this->guidedForm();
        abort_unless($form, 404);

        $request->merge(['phone' => \App\Support\Phone::normalize($request->input('phone'))]);
        $fixed = $request->validate([
            'submission_key' => 'required|uuid',
            'company_name' => 'required|string|max:150',
            'company_size' => ['required', Rule::in(array_keys(BusinessCompany::SIZES))],
            'phone' => ['required', 'string', 'max:30', \App\Support\Phone::RULE],
            'email' => 'required|email:rfc|max:150',
            'consent' => 'accepted',
        ], [
            'submission_key.*' => 'انتهت صلاحية الصفحة، أعد تحميلها ثم أرسل.',
            'company_name.required' => 'اكتب اسم الشركة',
            'company_size.required' => 'اختر حجم الشركة',
            'phone.required' => 'اكتب رقم الهاتف أو الواتساب',
            'phone.regex' => \App\Support\Phone::MESSAGE,
            'email.required' => 'اكتب البريد الإلكتروني',
            'email.email' => 'البريد الإلكتروني غير صحيح',
            'consent.accepted' => 'وافق على استخدام بياناتك للتواصل بشأن طلبك',
        ]);

        $submission = new RequestSubmission($form);
        $answers = $submission->validateAnswers($request);
        $rows = $submission->answerRows($answers, $request);
        $files = [];
        foreach ($rows as &$row) {
            foreach ($row['files'] as $file) {
                $files[] = [$row['question_id'], $file];
            }
            $row['files'] = count($row['files']);
        }
        unset($row);

        return $this->save($request, [
            'source' => BusinessRequest::SOURCE_GUIDED, 'submission_key' => $fixed['submission_key'], 'answers' => $rows,
            'phone' => $fixed['phone'], 'email' => $fixed['email'], 'company_name' => $fixed['company_name'],
            'company_size' => $fixed['company_size'], 'contact_name' => Auth::user()->name,
        ], $files);
    }

    // ── The company's area ─────────────────────────────────────────────

    public function mine()
    {
        $userId = Auth::id();

        return view('website.business.mine', [
            'requests' => BusinessRequest::where('user_id', $userId)->latest('id')->get(),
            'projects' => BusinessProject::where('user_id', $userId)->withCount('tasks')->with('tasks:id,business_project_id,status')->latest('id')->get(),
            'company' => BusinessCompany::where('user_id', $userId)->first(),
        ]);
    }

    public function showRequest(BusinessRequest $businessRequest)
    {
        $this->own($businessRequest->user_id);
        $businessRequest->load(['files', 'project', 'events' => fn ($q) => $q->where('internal', false)]);

        return view('website.business.request', ['item' => $businessRequest]);
    }

    public function decide(Request $request, BusinessRequest $businessRequest)
    {
        $this->own($businessRequest->user_id);
        $data = $request->validate(['decision' => ['required', Rule::in(['accept', 'reject'])], 'note' => 'nullable|string|max:1000']);
        if (! $businessRequest->awaitsClientDecision()) {
            return back()->withErrors(['decision' => 'لا يوجد عرض سعر بانتظار قرارك']);
        }
        $this->business->decideQuote($businessRequest, $data['decision'] === 'accept', $data['note'] ?? null, Auth::user());

        return back()->with('Success_message', $data['decision'] === 'accept'
            ? 'شكرًا! اعتمدت عرض السعر، وسيبدأ الفريق تجهيز مشروعك.' : 'سجّلنا رفضك لعرض السعر، وسيتواصل معك الفريق.');
    }

    public function pay(BusinessRequest $businessRequest, OnlinePaymentService $payments)
    {
        $this->own($businessRequest->user_id);
        if ($businessRequest->isPaid() || ! $businessRequest->hasQuote()
            || ! in_array($businessRequest->status, [BusinessRequest::STATUS_ACCEPTED, BusinessRequest::STATUS_CONVERTED], true)) {
            return back()->withErrors(['pay' => 'الدفع متاح بعد اعتماد عرض السعر، ولم يُدفع بعد.']);
        }
        try {
            return redirect()->away($payments->start(OnlinePayment::PURPOSE_BUSINESS, Auth::user(), round((float) $businessRequest->quote_amount, 2),
                'نفّذها للأعمال — ' . $businessRequest->number, ['business_request_id' => $businessRequest->id, 'back_url' => route('business.requests.show', $businessRequest)]));
        } catch (\Throwable $e) {
            return $this->payment_error($e);
        }
    }

    public function showProject(BusinessProject $businessProject)
    {
        $this->own($businessProject->user_id);
        $businessProject->load(['tasks', 'request', 'deliveries.files', 'events' => fn ($q) => $q->where('internal', false)->with('user:id,name')]);

        return view('website.business.project', ['project' => $businessProject]);
    }

    public function review(Request $request, BusinessDelivery $delivery)
    {
        $this->own($delivery->project->user_id);
        $data = $request->validate(['decision' => ['required', Rule::in(['approve', 'changes'])], 'comment' => 'nullable|string|max:3000'],
            ['decision.required' => 'اختر: اعتماد أو طلب تعديل']);
        try {
            $this->business->review($delivery, $data['decision'] === 'approve', $data['comment'] ?? null, Auth::user());
        } catch (\RuntimeException $e) {
            return back()->withErrors(['comment' => $e->getMessage()])->withInput();
        }

        return redirect(route('business.projects.show', $delivery->project) . '#delivery-' . $delivery->id)
            ->with('Success_message', $data['decision'] === 'approve' ? 'تم اعتماد النسخة.' : 'أرسلنا طلب التعديل للفريق.');
    }

    public function requestFile(BusinessRequestFile $file)
    {
        $owner = $file->request?->user_id;
        abort_unless(Auth::user()->account_type === \App\Models\User::TYPE_ADMIN || ($owner && (int) $owner === (int) Auth::id()), 404);

        return Storage::disk(BusinessRequestFile::DISK)->download($file->path, $file->original_name);
    }

    public function deliveryFile(BusinessDeliveryFile $file)
    {
        $owner = $file->delivery?->project?->user_id;
        abort_unless(Auth::user()->account_type === \App\Models\User::TYPE_ADMIN || ($owner && (int) $owner === (int) Auth::id()), 404);

        return Storage::disk(BusinessDeliveryFile::DISK)->download($file->path, $file->original_name);
    }

    // ── Helpers ────────────────────────────────────────────────────────

    private function save(Request $request, array $data, array $files)
    {
        $user = Auth::user();
        if ($existing = $this->business->alreadySent($data['submission_key'], $user, $request->ip())) {
            return redirect()->route('business.index')->with('business_created', $existing->number);
        }
        $limitKey = 'business:' . ($user ? 'user:' . $user->id : 'session:' . $request->session()->getId());
        if (RateLimiter::tooManyAttempts($limitKey, self::SENT_PER_HOUR)) {
            return back()->withInput()->withErrors(['details' => 'أرسلت عدة طلبات خلال وقت قصير. حاول مرة أخرى بعد قليل (ما كتبته محفوظ).']);
        }
        [$saved, $created] = $this->business->submit($data, $user, $request->ip(), $files);
        if ($created) {
            RateLimiter::hit($limitKey, 3600);
        }

        return redirect()->route($data['source'] === BusinessRequest::SOURCE_GUIDED ? 'business.requests.show' : 'business.index',
            $data['source'] === BusinessRequest::SOURCE_GUIDED ? $saved : [])->with('business_created', $saved->number);
    }

    private function guidedForm(): ?ServiceForm
    {
        $service = BusinessFormSeeder::service();

        return $service ? ServiceForm::published()->where('sub_category_id', $service->id)->with('sections.questions')->first() : null;
    }

    private function freeMax(): int
    {
        $max = (int) (SiteContent::first('business.page')['free_max'] ?? 0);

        return $max >= 500 && $max <= BusinessRequest::FREE_TEXT_MAX ? $max : BusinessRequest::FREE_TEXT_MAX;
    }

    /** Owner in the check: another company's number is simply not found. */
    private function own(?int $ownerId): void
    {
        abort_unless($ownerId && (int) $ownerId === (int) Auth::id(), 404);
    }
}
