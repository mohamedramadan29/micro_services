<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\admin\SubCategory;
use App\Models\RequestOffer;
use App\Models\RequestOfferFile;
use App\Models\RequestPayment;
use App\Models\ServiceRequest;
use App\Services\Payments\RequestPaymentService;
use App\Services\ServiceRequests\RequestAdminService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use RuntimeException;

/** Pages 7, 15 (#14) and 18–19 of the spec: request lists, follow-up and results. */
class ServiceRequestAdminController extends Controller
{
    public const TABS = [
        'active' => 'الطلبات النشطة',
        'inquiries' => 'استفسارات الأسعار',
        'followup' => 'تنبيهات المتابعة',
        'completed' => 'تم تنفيذ الطلبات',
        'not_completed' => 'لم يتم تنفيذ الطلبات',
        'refunds' => 'دفعات تحتاج استرجاعًا',
    ];

    public function __construct(private RequestAdminService $admin, private RequestPaymentService $payments)
    {
    }

    public function index(Request $request)
    {
        $tab = array_key_exists((string) $request->string('tab'), self::TABS) ? (string) $request->string('tab') : 'active';

        $stats = [
            'open' => ServiceRequest::where('status', ServiceRequest::STATUS_OPEN)->count(),
            'in_progress' => ServiceRequest::where('status', ServiceRequest::STATUS_IN_PROGRESS)->count(),
            'completed' => ServiceRequest::where('status', ServiceRequest::STATUS_COMPLETED)->count(),
            'not_completed' => ServiceRequest::where('status', ServiceRequest::STATUS_NOT_COMPLETED)->count(),
            'inquiries' => ServiceRequest::where('status', ServiceRequest::STATUS_INQUIRY)->count(),
            'followup' => ServiceRequest::whereNotNull('no_offer_alerted_at')->where('followup_status', '!=', ServiceRequest::FOLLOWUP_RESOLVED)->count(),
            'refunds' => RequestPayment::where('status', RequestPayment::STATUS_CONFLICT)->count(),
        ];

        if ($tab === 'refunds') {
            $payments = RequestPayment::where('status', RequestPayment::STATUS_CONFLICT)->with(['request', 'client', 'offer.freelancer'])->latest()->paginate(20);

            return view('admin.service-requests.index', compact('tab', 'stats', 'payments') + ['requests' => null, 'services' => collect(), 'filters' => []]);
        }

        $filters = $request->only(['q', 'service', 'status', 'reason', 'from', 'to', 'offers', 'followup', 'freelancer', 'source']);
        $query = match ($tab) {
            'inquiries' => ServiceRequest::where('status', ServiceRequest::STATUS_INQUIRY),
            'followup' => ServiceRequest::whereNotNull('no_offer_alerted_at'),
            'completed' => ServiceRequest::where('status', ServiceRequest::STATUS_COMPLETED),
            'not_completed' => ServiceRequest::where('status', ServiceRequest::STATUS_NOT_COMPLETED),
            default => ServiceRequest::whereIn('status', [ServiceRequest::STATUS_OPEN, ServiceRequest::STATUS_IN_PROGRESS]),
        };
        $requests = $this->applyFilters($query, $filters, $tab)
            ->with(['user', 'acceptedOffer.freelancer'])
            ->withCount(['offers as offers_count' => fn ($q) => $q->where('status', '!=', RequestOffer::STATUS_WITHDRAWN)])
            ->paginate(20)->withQueryString();

        $services = SubCategory::whereIn('id', ServiceRequest::query()->select('sub_category_id'))->orderBy('name')->get(['id', 'name']);

        return view('admin.service-requests.index', compact('tab', 'stats', 'requests', 'services', 'filters') + ['payments' => null]);
    }

    public function show(ServiceRequest $serviceRequest)
    {
        $serviceRequest->load(['user', 'answers', 'files', 'closedBy', 'payments.freelancer', 'offers.freelancer', 'offers.files', 'offers.messages.sender', 'adminMessages.sender', 'events.user', 'events.freelancer']);
        $serviceRequest->adminMessages()->where('from_admin', false)->whereNull('read_at')->update(['read_at' => now()]);
        $filesByQuestion = $serviceRequest->files->groupBy(fn ($file) => $file->question_id ?? 'attachments');
        $clientRequests = ServiceRequest::where('user_id', $serviceRequest->user_id)->count();
        $ledger = \App\Models\WalletTransaction::whereIn('request_payment_id', $serviceRequest->payments->pluck('id'))->with('user')->oldest('id')->get();

        $matchingFreelancers = $serviceRequest->isOpen() ? $this->admin->matchingFreelancers($serviceRequest) : collect();

        return view('admin.service-requests.show', compact('serviceRequest', 'filesByQuestion', 'clientRequests', 'ledger', 'matchingFreelancers'));
    }

    /** Page 7: invite a freelancer of the request's service to send an offer. */
    public function invite(ServiceRequest $serviceRequest, \App\Models\User $freelancer)
    {
        return $this->attempt(fn () => $this->admin->invite($serviceRequest, $freelancer), 'تم إرسال الدعوة للمستقل ' . $freelancer->name);
    }

    /** The team writes to the client (a question, prices, missing details). */
    public function sendMessage(Request $request, ServiceRequest $serviceRequest)
    {
        $data = $request->validate(['body' => 'required|string|max:3000'], ['body.required' => 'اكتب الرسالة أولًا']);

        $serviceRequest->adminMessages()->create(['sender_id' => Auth::id(), 'from_admin' => true, 'body' => $data['body']]);
        $serviceRequest->user->notify(new \App\Notifications\RequestUpdateNotification(
            \App\Support\Msg::t('requests.team_message', ['number' => $serviceRequest->number]),
            '/my/requests/' . $serviceRequest->number . '#team-messages',
            'team_messages'
        ));

        return redirect()->to(route('admin.requests.show', $serviceRequest) . '#messages')->with('success', 'تم إرسال الرسالة للعميل');
    }

    public function followup(Request $request, ServiceRequest $serviceRequest)
    {
        $data = $request->validate([
            'followup_status' => ['required', Rule::in(array_keys(ServiceRequest::FOLLOWUP_STATUSES))],
            'followup_note' => 'nullable|string|max:2000',
        ]);
        $this->admin->updateFollowup($serviceRequest, $data['followup_status'], $data['followup_note'] ?? null);

        return back()->with('success', 'تم تحديث المتابعة');
    }

    public function close(Request $request, ServiceRequest $serviceRequest)
    {
        $data = $request->validate([
            'reason' => ['required', Rule::in(array_keys(ServiceRequest::CLOSE_REASONS))],
            'note' => 'nullable|required_if:reason,other|string|max:2000',
        ], ['reason.required' => 'اختر سبب الإغلاق', 'note.required_if' => 'اكتب السبب عند اختيار «سبب آخر»']);

        return $this->attempt(fn () => $this->admin->close($serviceRequest, $data['reason'], $data['note'] ?? null, Auth::user()),
            'تم نقل الطلب إلى «لم يتم تنفيذ الطلبات»' . ($serviceRequest->isInProgress() ? ' وتسجيل استرجاع المبلغ المحجوز' : ''));
    }

    public function publish(Request $request, ServiceRequest $serviceRequest)
    {
        $request->validate(['client_agreed' => 'accepted'], ['client_agreed.accepted' => 'أكّد أن العميل وافق على نشر طلبه']);

        return $this->attempt(fn () => $this->admin->publishInquiry($serviceRequest, Auth::user()), 'تم نشر الطلب للمستقلين المختصين');
    }

    public function complete(Request $request, ServiceRequest $serviceRequest)
    {
        $data = $request->validate(['note' => 'required|string|max:2000'], ['note.required' => 'وثّق سبب اعتماد التنفيذ نيابة عن العميل']);

        return $this->attempt(fn () => $this->admin->completeOnBehalf($serviceRequest, $data['note'], Auth::user()), 'تم اعتماد التنفيذ وتحويل المستحقات للمستقل');
    }

    public function refund(RequestPayment $payment)
    {
        return $this->attempt(fn () => $this->payments->refund($payment), 'تم تسجيل استرجاع المبلغ للعميل');
    }

    public function offerFile(RequestOffer $offer, RequestOfferFile $file)
    {
        abort_unless((int) $file->request_offer_id === (int) $offer->id, 404);
        abort_unless(Storage::disk(RequestOfferFile::DISK)->exists($file->path), 404);

        return Storage::disk(RequestOfferFile::DISK)->download($file->path, $file->original_name);
    }

    public function readAllNotifications()
    {
        Auth::user()->unreadNotifications->markAsRead();

        return back();
    }

    private function attempt(callable $action, string $success)
    {
        try {
            $action();
        } catch (RuntimeException $e) {
            return back()->withErrors($e->getMessage());
        } catch (\Throwable $e) {
            Log::error('Admin request action failed', ['error' => $e->getMessage()]);

            return back()->withErrors('تعذّر تنفيذ العملية: ' . $e->getMessage());
        }

        return back()->with('success', $success);
    }

    private function applyFilters(Builder $query, array $filters, string $tab): Builder
    {
        $filled = fn ($key) => isset($filters[$key]) && $filters[$key] !== '';

        if ($filled('q')) {
            $term = '%' . str_replace(['%', '_'], ['\%', '\_'], $filters['q']) . '%';
            $query->where(fn ($q) => $q->where('number', 'like', $term)
                ->orWhere('service_name', 'like', $term)
                ->orWhere('contact_name', 'like', $term)
                ->orWhere('contact_email', 'like', $term)
                ->orWhere('contact_phone', 'like', $term));
        }
        if ($filled('service')) {
            $query->where('sub_category_id', (int) $filters['service']);
        }
        if ($filled('source') && array_key_exists($filters['source'], ServiceRequest::SOURCES)) {
            $query->where('source', $filters['source']);
        }
        // Step 19: by the chosen freelancer (name, username or email).
        if ($filled('freelancer')) {
            $term = '%' . str_replace(['%', '_'], ['\%', '\_'], $filters['freelancer']) . '%';
            $query->whereHas('acceptedOffer.freelancer', fn ($q) => $q->where('name', 'like', $term)->orWhere('user_name', 'like', $term)->orWhere('email', 'like', $term));
        }
        if ($filled('status') && $tab === 'active' && in_array($filters['status'], [ServiceRequest::STATUS_OPEN, ServiceRequest::STATUS_IN_PROGRESS], true)) {
            $query->where('status', $filters['status']);
        }
        if ($filled('reason') && $tab === 'not_completed') {
            $query->where('closed_reason', $filters['reason']);
        }
        if ($filled('followup') && in_array($tab, ['followup', 'inquiries'], true)) {
            // An inquiry nobody touched yet has no status and counts as «جديد».
            $filters['followup'] === ServiceRequest::FOLLOWUP_NEW && $tab === 'inquiries'
                ? $query->where(fn ($q) => $q->whereNull('followup_status')->orWhere('followup_status', ServiceRequest::FOLLOWUP_NEW))
                : $query->where('followup_status', $filters['followup']);
        }
        if ($filled('from')) {
            $query->whereDate('created_at', '>=', $filters['from']);
        }
        if ($filled('to')) {
            $query->whereDate('created_at', '<=', $filters['to']);
        }
        if ($filled('offers')) {
            $filters['offers'] === 'none'
                ? $query->whereDoesntHave('offers', fn ($q) => $q->where('status', '!=', RequestOffer::STATUS_WITHDRAWN))
                : $query->whereHas('offers', fn ($q) => $q->where('status', '!=', RequestOffer::STATUS_WITHDRAWN));
        }

        return match ($tab) {
            'completed' => $query->orderByDesc('completed_at'),
            'not_completed' => $query->orderByDesc('closed_at'),
            'followup' => $query->orderByRaw("CASE WHEN followup_status = 'resolved' THEN 1 ELSE 0 END")->orderByDesc('no_offer_alerted_at'),
            default => $query->orderByDesc('id'),
        };
    }
}
