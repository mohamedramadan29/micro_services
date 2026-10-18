<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Contract;
use App\Models\ContractDispute;
use App\Models\ContractFreelancerChange;
use App\Models\ContractStage;
use Illuminate\Http\Request;

/** Service 3 for the admin: contracts, open disputes, change requests and late stages (decisions on each contract page). */
class ContractAdminController extends Controller
{
    public const TABS = [
        'all' => 'كل العقود',
        'disputes' => 'خلافات مفتوحة',
        'changes' => 'طلبات تغيير المستقل',
        'late' => 'مراحل متأخرة',
        'reviews' => 'مراجعات تجاوزت المهلة',
        'pauses' => 'إيقافات بلا رد',
        'addenda' => 'ملاحق مقترحة',
    ];

    public function index(Request $request)
    {
        $tab = array_key_exists((string) $request->string('tab'), self::TABS) ? (string) $request->string('tab') : 'all';
        $query = Contract::with(['client', 'freelancer', 'request', 'stages.deliveries', 'stages.pauses', 'disputes', 'freelancerChanges', 'agreements', 'addenda'])
            ->addSelect(['last_activity' => \App\Models\ServiceRequestEvent::select('created_at')->whereColumn('service_request_id', 'contracts.service_request_id')->latest('id')->limit(1)])
            ->latest('id');
        match ($tab) {
            'disputes' => $query->whereHas('disputes', fn ($q) => $q->whereIn('status', ContractDispute::OPEN_STATUSES)),
            'changes' => $query->whereHas('freelancerChanges', fn ($q) => $q->where('status', ContractFreelancerChange::STATUS_PENDING)),
            'late' => $query->where('status', Contract::STATUS_ACTIVE)->whereHas('stages', fn ($q) => $q->lateNow()),
            'reviews' => $query->whereHas('stages.deliveries', fn ($q) => $q->where('status', 'submitted')->whereNotNull('escalated_at')),
            'pauses' => $query->whereHas('stages.pauses', fn ($q) => $q->where('status', \App\Models\ContractPause::STATUS_ACTIVE)->whereNotNull('escalated_at')),
            'addenda' => $query->whereHas('addenda', fn ($q) => $q->where('status', \App\Models\ContractAddendum::STATUS_PROPOSED)),
            default => null,
        };
        if ($request->filled('q')) {
            $term = '%' . str_replace(['%', '_'], ['\%', '\_'], $request->query('q')) . '%';
            $query->where(fn ($q) => $q->where('number', 'like', $term)->orWhere('title', 'like', $term)
                ->orWhereHas('client', fn ($u) => $u->where('name', 'like', $term)->orWhere('email', 'like', $term))
                ->orWhereHas('freelancer', fn ($u) => $u->where('name', 'like', $term)->orWhere('email', 'like', $term)));
        }
        if ($request->filled('service')) {
            $query->whereHas('request', fn ($q) => $q->where('sub_category_id', (int) $request->query('service')));
        }
        if (array_key_exists((string) $request->string('status'), Contract::STATUSES)) {
            $query->where('status', $request->query('status'));
        }
        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->query('from'));
        }
        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->query('to'));
        }
        $sort = $request->query('sort');
        if ($sort === 'value') {
            $query->reorder()->orderByDesc(ContractStage::selectRaw('COALESCE(SUM(amount), 0)')->whereColumn('contract_stages.contract_id', 'contracts.id'));
        }

        $counts = [
            'disputes' => ContractDispute::whereIn('status', ContractDispute::OPEN_STATUSES)->count(),
            'changes' => ContractFreelancerChange::where('status', ContractFreelancerChange::STATUS_PENDING)->count(),
            'late' => ContractStage::lateNow()->whereHas('contract', fn ($q) => $q->where('status', Contract::STATUS_ACTIVE))->count(),
        ];

        // «Who it waits for» is worked out from the loaded state, so this filter runs on the matches.
        $waiting = array_key_exists((string) $request->string('waiting'), Contract::WAITING_ON) ? (string) $request->string('waiting') : null;
        if ($waiting || $sort === 'due') {
            $all = $query->get();
            if ($waiting) {
                $all = $all->filter(fn (Contract $c) => ($c->waitingOn()[0] ?? null) === $waiting);
            }
            if ($sort === 'due') {
                $all = $all->sortBy(fn (Contract $c) => $c->currentStageLoaded()?->due_at?->timestamp ?? PHP_INT_MAX);
            }
            $page = \Illuminate\Pagination\Paginator::resolveCurrentPage();
            $contracts = new \Illuminate\Pagination\LengthAwarePaginator($all->forPage($page, 25)->values(), $all->count(), 25, $page, ['path' => $request->url(), 'query' => $request->query()]);
        } else {
            $contracts = $query->paginate(25)->withQueryString();
        }
        $counts += [
            'pauses' => \App\Models\ContractPause::where('status', \App\Models\ContractPause::STATUS_ACTIVE)->whereNotNull('escalated_at')->count(),
            'addenda' => \App\Models\ContractAddendum::where('status', \App\Models\ContractAddendum::STATUS_PROPOSED)->count(),
        ];

        $services = \App\Models\admin\SubCategory::whereIn('id', \App\Models\ServiceRequest::whereIn('id', Contract::select('service_request_id'))->select('sub_category_id'))->orderBy('name')->get(['id', 'name']);

        return view('admin.contracts.index', ['contracts' => $contracts, 'tab' => $tab, 'counts' => $counts, 'services' => $services]);
    }
}
