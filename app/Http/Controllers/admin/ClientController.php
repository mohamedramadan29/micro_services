<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\RequestOffer;
use App\Models\RequestPayment;
use App\Models\ServiceRequest;
use App\Models\User;
use App\Models\admin\SubCategory;
use App\Services\ServiceRequests\ClientClassifier;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

/** Spec pages 8 and 20: clients with their classifications and full record. */
class ClientController extends Controller
{
    /**
     * Client accounts that registered but have not sent anything yet. Not one of the spec's seven
     * classifications (those need a request), so it is listed apart and added to «كل العملاء».
     */
    public const NO_REQUESTS = 'no_requests';

    public function __construct(private ClientClassifier $classifier)
    {
    }

    public function index(Request $request)
    {
        $classified = $this->classifier->cached();
        $counts = $this->classifier->counts($classified);
        $registered = $this->registeredWithoutRequests($classified);
        $counts[self::NO_REQUESTS] = $registered->count();
        $category = (string) $request->string('category');
        $category = array_key_exists($category, ClientClassifier::CATEGORIES) || $category === self::NO_REQUESTS ? $category : null;

        // Filter and order ids from the (cached) classification; only the page's users are loaded.
        $rows = match (true) {
            $category === self::NO_REQUESTS => $registered,
            $category !== null => $classified->filter(fn ($row) => in_array($category, $row['tags'], true)),
            default => $classified->union($registered),
        };
        // A search sent as an array (?q[]=…) is ignored instead of breaking the page.
        $search = is_string($request->query('q')) ? trim($request->query('q')) : '';
        if ($search !== '') {
            $term = '%' . str_replace(['%', '_'], ['\%', '\_'], $search) . '%';
            $matching = User::whereIn('id', $rows->keys())
                ->where(fn ($q) => $q->where('name', 'like', $term)->orWhere('email', 'like', $term)->orWhere('phone', 'like', $term)
                    ->orWhereIn('id', ServiceRequest::where('contact_phone', 'like', $term)->orWhere('contact_email', 'like', $term)->select('user_id')))
                ->pluck('id')->all();
            $rows = $rows->only($matching);
        }
        // Step 20: clients who asked for a given service.
        $service = $request->integer('service') ?: null;
        if ($service) {
            $rows = $rows->only(ServiceRequest::where('sub_category_id', $service)->distinct()->pluck('user_id')->all());
        }
        $rows = $rows->sortByDesc(fn ($row) => $row['last_activity']?->timestamp ?? 0);

        $page = LengthAwarePaginator::resolveCurrentPage();
        $pageRows = $rows->forPage($page, 20);
        $users = User::whereIn('id', $pageRows->keys())->get()->keyBy('id');
        $items = $pageRows->map(fn ($row, $id) => isset($users[$id]) ? ['user' => $users[$id]] + $row : null)->filter()->values();

        $clients = new LengthAwarePaginator($items, $rows->count(), 20, $page, [
            'path' => $request->url(),
            'query' => $request->query(),
        ]);

        return view('admin.clients.index', [
            'clients' => $clients,
            'counts' => $counts,
            'total' => $classified->count() + $registered->count(),
            'search' => $search,
            'category' => $category,
            'threshold' => $this->classifier->repeatThreshold(),
            'service' => $service,
            'services' => SubCategory::whereIn('id', ServiceRequest::query()->select('sub_category_id'))->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function show(User $user)
    {
        $classification = $this->classifier->classify([$user->id])->get($user->id);
        if (! $classification) {
            // A freelancer has his own page; a client account that has not sent anything yet still has a
            // profile here (registration, contact details, account state) — it used to be a 404.
            if ($user->isFreelancer()) {
                return redirect()->route('admin.freelancers.show', $user);
            }
            abort_unless($user->isClient(), 404, 'هذا الحساب ليس حساب عميل');
            $classification = self::emptyClassification();
        }

        $requests = ServiceRequest::where('user_id', $user->id)
            ->with(['acceptedOffer.freelancer', 'adminMessages' => fn ($q) => $q->latest('id')->limit(1)])
            ->withCount(['offers as offers_count' => fn ($q) => $q->where('status', '!=', RequestOffer::STATUS_WITHDRAWN)])
            ->latest()
            ->get();

        $paid = RequestPayment::where('client_id', $user->id)->whereIn('status', [RequestPayment::STATUS_PAID, RequestPayment::STATUS_RELEASED])->sum('amount');
        $latestContact = $requests->sortByDesc('created_at')->first();

        // Free site checks of this client: by account, or by the email left on the report form.
        $siteTests = \App\Models\SiteTest::where('user_id', $user->id)
            ->when($user->email, fn ($q) => $q->orWhere('contact_email', $user->email))
            ->latest('id')->limit(20)->get();

        // Everything else the client did on the site, so a new account is not an empty page.
        $helpRequests = \App\Models\HelpRequest::where('user_id', $user->id)->latest('id')->limit(20)->get();
        $suggestions = \App\Models\Suggestion::where('user_id', $user->id)->latest('id')->limit(20)->get();

        return view('admin.clients.show', compact('user', 'classification', 'requests', 'paid', 'latestContact', 'siteTests', 'helpRequests', 'suggestions'));
    }

    /** Client accounts with no request (or counted help request) yet, newest registration first. */
    private function registeredWithoutRequests(Collection $classified): Collection
    {
        return User::where('account_type', User::TYPE_CLIENT)
            ->whereNotIn('id', $classified->keys())
            ->orderByDesc('created_at')
            ->pluck('created_at', 'id')
            ->map(fn ($registeredAt) => ['new_account' => true] + self::emptyClassification($registeredAt ? \Illuminate\Support\Carbon::parse($registeredAt) : null));
    }

    private static function emptyClassification(?\Illuminate\Support\Carbon $lastActivity = null): array
    {
        return ['tags' => [], 'requests' => 0, 'contacts' => 0, 'completed' => 0, 'last_activity' => $lastActivity];
    }
}
