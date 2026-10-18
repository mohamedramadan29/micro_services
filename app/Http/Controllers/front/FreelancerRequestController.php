<?php

namespace App\Http\Controllers\front;

use App\Http\Controllers\Controller;
use App\Models\RequestOffer;
use App\Models\RequestOfferFile;
use App\Models\ServiceRequest;
use App\Models\ServiceRequestFile;
use App\Notifications\NewOfferNotification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

/** The freelancer side: browse matching requests, send and manage offers. */
class FreelancerRequestController extends Controller
{
    /** Filters on the board, also used to keep the query string in links. */
    private const FILTERS = ['q', 'category', 'service', 'budget_from', 'budget_to', 'deadline', 'published', 'project_type', 'work_mode', 'city', 'files', 'urgency', 'experience', 'sort'];

    private const DEADLINES = ['week' => 7, 'month' => 30, 'quarter' => 90];
    private const PUBLISHED = ['today' => 1, 'week' => 7, 'month' => 30];

    public function index(Request $request)
    {
        $user = Auth::user();
        abort_unless($user->isFreelancer(), 403, 'صفحة طلبات العملاء متاحة لحسابات المستقلين');

        $specializations = $user->specializations()->with('category')->orderBy('name')->get();
        $base = ServiceRequest::forFreelancer($user);

        $options = [];
        foreach (['project_type', 'work_mode', 'urgency', 'experience'] as $column) {
            $options[$column] = (clone $base)->whereNotNull($column)->distinct()->orderBy($column)->pluck($column);
        }

        $filters = $request->only(self::FILTERS);
        $query = $this->applyFilters(clone $base, $filters)
            ->withCount(['offers as offers_count' => fn ($q) => $q->where('status', '!=', RequestOffer::STATUS_WITHDRAWN)])
            ->withCount('files');
        $query = $this->applySort($query, $filters['sort'] ?? 'newest');

        $requests = $query->paginate(10)->withQueryString();
        $myOffers = RequestOffer::where('freelancer_id', $user->id)
            ->whereIn('service_request_id', $requests->pluck('id'))
            ->pluck('status', 'service_request_id');

        return view('website.freelancer-requests.index', compact('requests', 'specializations', 'options', 'filters', 'myOffers'));
    }

    public function show(ServiceRequest $serviceRequest)
    {
        $this->authorizeFreelancer($serviceRequest);
        $serviceRequest->load(['answers', 'files']);
        $filesByQuestion = $serviceRequest->files->groupBy(fn ($file) => $file->question_id ?? 'attachments');
        $offer = $serviceRequest->offers()->where('freelancer_id', Auth::id())->with('files')->first();
        $offersCount = $serviceRequest->offers()->where('status', '!=', RequestOffer::STATUS_WITHDRAWN)->count();

        return view('website.freelancer-requests.show', compact('serviceRequest', 'filesByQuestion', 'offer', 'offersCount'));
    }

    public function file(ServiceRequest $serviceRequest, ServiceRequestFile $file)
    {
        $this->authorizeFreelancer($serviceRequest);
        abort_unless((int) $file->service_request_id === (int) $serviceRequest->id, 404);
        abort_unless(Storage::disk(ServiceRequestFile::DISK)->exists($file->path), 404);

        return Storage::disk(ServiceRequestFile::DISK)->download($file->path, $file->original_name);
    }

    /** Send an offer, or update this freelancer's existing one on the request. */
    /** The chosen freelancer approves or rejects the client's change request. */
    public function respondChange(Request $request, ServiceRequest $serviceRequest, \App\Models\ServiceRequestChange $change, \App\Services\ServiceRequests\RequestChangeService $changes)
    {
        abort_unless((int) $change->service_request_id === (int) $serviceRequest->id
            && (int) $serviceRequest->acceptedOffer?->freelancer_id === (int) Auth::id(), 403);
        $data = $request->validate([
            'decision' => 'required|in:accept,reject',
            'note' => 'required_if:decision,reject|nullable|string|max:1000',
        ], ['note.required_if' => 'اكتب سبب الرفض ليعرفه العميل']);

        try {
            $changes->respond($change, Auth::user(), $data['decision'] === 'accept', $data['note'] ?? null);
        } catch (\RuntimeException $e) {
            return back()->withErrors($e->getMessage());
        }

        $message = $data['decision'] === 'reject' ? 'أُرسل رفضك وسببه للعميل.'
            : ($change->fresh()->status === \App\Models\ServiceRequestChange::STATUS_AWAITING_PAYMENT ? 'تمت الموافقة. يُعتمد التعديل بعد دفع العميل الفرق.' : 'تمت الموافقة واعتُمد التعديل.');

        return redirect()->to(route('freelancer.requests.show', $serviceRequest) . '#changes')->with('Success_message', $message);
    }

    public function saveOffer(Request $request, ServiceRequest $serviceRequest)
    {
        $this->authorizeFreelancer($serviceRequest);
        abort_unless($serviceRequest->isOpen(), 403, 'هذا الطلب لم يعد يستقبل عروضًا');
        if (! Auth::user()->isApprovedFreelancer()) {
            return back()->withErrors('تُفتح العروض بعد توثيق حسابك (تأكيد البريد) واعتماد ملفك من فريق نفّذها.');
        }

        $offer = $serviceRequest->offers()->where('freelancer_id', Auth::id())->first();
        if ($offer && ! in_array($offer->status, [...RequestOffer::ACTIVE_STATUSES, RequestOffer::STATUS_WITHDRAWN], true)) {
            return back()->withErrors('لا يمكن تعديل هذا العرض بعد قرار العميل');
        }

        $keptFiles = $offer ? $offer->files()->whereNotIn('id', (array) $request->input('remove_files', []))->count() : 0;
        $data = $request->validate([
            'price' => 'required|numeric|min:1|max:1000000',
            'duration_days' => 'required|integer|min:1|max:365',
            'scope' => 'required|string|min:20|max:3000',
            'message' => 'required|string|min:20|max:3000',
            'portfolio_links' => 'nullable|array|max:' . RequestOffer::MAX_LINKS,
            'portfolio_links.*' => 'nullable|url|max:500',
            'portfolio_files' => 'nullable|array|max:' . max(0, RequestOffer::MAX_FILES - $keptFiles),
            'portfolio_files.*' => 'file|max:' . (RequestOffer::FILE_MAX_MB * 1024) . '|mimes:' . implode(',', RequestOffer::FILE_EXTENSIONS),
            'remove_files' => 'nullable|array',
            'remove_files.*' => 'integer',
        ], [
            'price.required' => 'من فضلك أدخل السعر المقترح',
            'price.min' => 'السعر يجب أن يكون 1 على الأقل',
            'price.max' => 'السعر أكبر من المسموح',
            'duration_days.required' => 'من فضلك أدخل مدة التنفيذ بالأيام',
            'duration_days.min' => 'مدة التنفيذ يوم واحد على الأقل',
            'duration_days.max' => 'مدة التنفيذ يجب ألا تتجاوز 365 يومًا',
            'scope.required' => 'من فضلك اكتب نطاق العمل والتسليمات',
            'scope.min' => 'اشرح نطاق العمل بشكل أوضح (20 حرفًا على الأقل)',
            'message.required' => 'من فضلك اكتب رسالة العرض',
            'message.min' => 'اكتب رسالة أوضح للعميل (20 حرفًا على الأقل)',
            'portfolio_links.max' => 'الحد الأقصى ' . RequestOffer::MAX_LINKS . ' روابط',
            'portfolio_links.*.url' => 'رابط العمل غير صحيح',
            'portfolio_files.max' => 'الحد الأقصى ' . RequestOffer::MAX_FILES . ' ملفات لنماذج الأعمال',
            'portfolio_files.*.max' => 'حجم الملف يجب ألا يتجاوز ' . RequestOffer::FILE_MAX_MB . ' ميجابايت',
            'portfolio_files.*.mimes' => 'نوع الملف غير مسموح',
        ]);

        $paths = [];
        $isNew = ! $offer || $offer->status === RequestOffer::STATUS_WITHDRAWN;

        try {
            $offer = DB::transaction(function () use ($serviceRequest, $offer, $data, $request, $isNew, &$paths) {
                $attributes = [
                    'price' => $data['price'],
                    'duration_days' => $data['duration_days'],
                    'scope' => $data['scope'],
                    'message' => $data['message'],
                    'portfolio_links' => array_values(array_filter($data['portfolio_links'] ?? [])) ?: null,
                    'submitted_at' => now(), // compared with the request's edited_at («الطلب عُدّل بعد عرضك»)
                ];
                if ($isNew) {
                    $attributes += ['status' => RequestOffer::STATUS_NEW, 'viewed_at' => null, 'is_saved' => false];
                }
                $offer = $offer
                    ? tap($offer)->update($attributes)
                    : $serviceRequest->offers()->create($attributes + ['freelancer_id' => Auth::id()]);

                foreach ($offer->files()->whereIn('id', $data['remove_files'] ?? [])->get() as $file) {
                    Storage::disk(RequestOfferFile::DISK)->delete($file->path);
                    $file->delete();
                }
                foreach ((array) $request->file('portfolio_files', []) as $file) {
                    $path = $file->store('request-offers/' . $offer->id, RequestOfferFile::DISK);
                    $paths[] = $path;
                    $offer->files()->create([
                        'original_name' => mb_substr($file->getClientOriginalName(), 0, 255),
                        'path' => $path,
                        'mime' => $file->getClientMimeType(),
                        'size' => $file->getSize(),
                    ]);
                }

                return $offer;
            });
        } catch (\Throwable $e) {
            Storage::disk(RequestOfferFile::DISK)->delete($paths);
            throw $e;
        }

        $serviceRequest->user->notify(new NewOfferNotification($offer, ! $isNew));
        if ($isNew) {
            $serviceRequest->resolveNoOfferAlert();
        }

        return redirect()->route('freelancer.requests.show', $serviceRequest)
            ->with('Success_message', $isNew ? 'تم إرسال عرضك للعميل' : 'تم تعديل عرضك');
    }

    public function withdraw(RequestOffer $offer)
    {
        abort_unless((int) $offer->freelancer_id === (int) Auth::id(), 403);
        if (! $offer->isActive()) {
            return back()->withErrors('لا يمكن سحب هذا العرض');
        }

        $offer->update(['status' => RequestOffer::STATUS_WITHDRAWN, 'is_saved' => false]);

        return back()->with('Success_message', 'تم سحب عرضك، ويمكنك إرسال عرض جديد طالما الطلب مفتوح');
    }

    public function myOffers()
    {
        abort_unless(Auth::user()->isFreelancer(), 403);

        $offers = RequestOffer::where('freelancer_id', Auth::id())
            ->with('request')
            ->withCount(['messages as unread_count' => fn ($q) => $q->whereNull('read_at')->where('sender_id', '!=', Auth::id())])
            ->latest('updated_at')
            ->paginate(10);

        return view('website.freelancer-requests.offers', compact('offers'));
    }

    private function authorizeFreelancer(ServiceRequest $serviceRequest): void
    {
        abort_unless($serviceRequest->visibleToFreelancer(Auth::user()), 403, 'هذا الطلب غير متاح لك');
    }

    private function applyFilters(Builder $query, array $filters): Builder
    {
        $filled = fn ($key) => isset($filters[$key]) && $filters[$key] !== '' && $filters[$key] !== null;

        if ($filled('q')) {
            $term = '%' . str_replace(['%', '_'], ['\%', '\_'], $filters['q']) . '%';
            $query->where(fn ($q) => $q->where('service_name', 'like', $term)
                ->orWhere('problem', 'like', $term)
                ->orWhereHas('answers', fn ($a) => $a->where('value', 'like', $term)));
        }
        if ($filled('category')) {
            $query->where('category_id', (int) $filters['category']);
        }
        if ($filled('service')) {
            $query->where('sub_category_id', (int) $filters['service']);
        }
        if ($filled('budget_from') && is_numeric($filters['budget_from'])) {
            $query->where(fn ($q) => $q->where('budget_max', '>=', $filters['budget_from'])
                ->orWhere(fn ($q) => $q->whereNull('budget_max')->where('budget_min', '>=', $filters['budget_from'])));
        }
        if ($filled('budget_to') && is_numeric($filters['budget_to'])) {
            $query->whereNotNull('budget_min')->where('budget_min', '<=', $filters['budget_to']);
        }
        if ($filled('deadline') && isset(self::DEADLINES[$filters['deadline']])) {
            $query->whereNotNull('deadline')->where('deadline', '<=', now()->addDays(self::DEADLINES[$filters['deadline']])->toDateString());
        }
        if ($filled('published') && isset(self::PUBLISHED[$filters['published']])) {
            $query->where('published_at', '>=', now()->subDays(self::PUBLISHED[$filters['published']]));
        }
        foreach (['project_type', 'work_mode', 'urgency', 'experience'] as $column) {
            if ($filled($column)) {
                $query->where($column, $filters[$column]);
            }
        }
        if ($filled('city')) {
            $query->where('city', 'like', '%' . str_replace(['%', '_'], ['\%', '\_'], $filters['city']) . '%');
        }
        if ($filled('files')) {
            $filters['files'] === 'yes' ? $query->has('files') : $query->doesntHave('files');
        }

        return $query;
    }

    /**
     * Board order. "best" (الأنسب) puts the requests where the freelancer has
     * the best chance first: fewest offers so far, then the newest.
     */
    private function applySort(Builder $query, string $sort): Builder
    {
        return match ($sort) {
            'best' => $query->orderBy('offers_count')->orderByDesc('published_at')->orderByDesc('id'),
            'budget_high' => $query->orderByRaw('budget_max IS NULL')->orderByDesc('budget_max'),
            'budget_low' => $query->orderByRaw('budget_min IS NULL')->orderBy('budget_min'),
            'deadline' => $query->orderByRaw('deadline IS NULL')->orderBy('deadline'),
            default => $query->orderByDesc('published_at')->orderByDesc('id'),
        };
    }
}
