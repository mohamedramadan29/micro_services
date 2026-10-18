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

/** Spec pages 8 and 20: clients with their classifications and full record. */
class ClientController extends Controller
{
    public function __construct(private ClientClassifier $classifier)
    {
    }

    public function index(Request $request)
    {
        $classified = $this->classifier->cached();
        $counts = $this->classifier->counts($classified);
        $category = array_key_exists((string) $request->string('category'), ClientClassifier::CATEGORIES) ? (string) $request->string('category') : null;

        // Filter and order ids from the (cached) classification; only the page's users are loaded.
        $rows = $classified
            ->when($category, fn ($rows) => $rows->filter(fn ($row) => in_array($category, $row['tags'], true)));
        if ($request->filled('q')) {
            $term = '%' . str_replace(['%', '_'], ['\%', '\_'], $request->query('q')) . '%';
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
            'total' => $classified->count(),
            'category' => $category,
            'threshold' => $this->classifier->repeatThreshold(),
            'service' => $service,
            'services' => SubCategory::whereIn('id', ServiceRequest::query()->select('sub_category_id'))->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function show(User $user)
    {
        $classification = $this->classifier->classify([$user->id])->get($user->id);
        abort_unless($classification, 404, 'هذا المستخدم ليس لديه طلبات');

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

        return view('admin.clients.show', compact('user', 'classification', 'requests', 'paid', 'latestContact', 'siteTests'));
    }
}
