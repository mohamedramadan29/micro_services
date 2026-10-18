<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\admin\SubCategory;
use App\Models\HelpRequest;
use App\Models\User;
use App\Notifications\HelpRequestNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

/** «طلبات المساعدة»: «ساعدوني أختار» requests, kept apart from service requests (brief pages 6 and 10). */
class HelpRequestAdminController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->only(['q', 'status', 'service', 'assignee', 'from', 'to']);
        $requests = HelpRequest::with(['user', 'assignee'])
            ->when($request->filled('q'), function ($query) use ($request) {
                $term = '%' . str_replace(['%', '_'], ['\%', '\_'], $request->query('q')) . '%';
                $query->where(fn ($q) => $q->where('number', 'like', $term)->orWhere('whatsapp', 'like', $term)
                    ->orWhere('service_name', 'like', $term)->orWhere('message', 'like', $term)
                    ->orWhereHas('user', fn ($u) => $u->where('name', 'like', $term)->orWhere('email', 'like', $term)));
            })
            ->when(isset(HelpRequest::STATUSES[$request->query('status')]), fn ($q) => $q->where('status', $request->query('status')))
            ->when($request->query('service') === 'general', fn ($q) => $q->whereNull('sub_category_id'))
            ->when(ctype_digit((string) $request->query('service')), fn ($q) => $q->where('sub_category_id', (int) $request->query('service')))
            ->when($request->query('assignee') === 'none', fn ($q) => $q->whereNull('assigned_to'))
            ->when(ctype_digit((string) $request->query('assignee')), fn ($q) => $q->where('assigned_to', (int) $request->query('assignee')))
            ->when($request->filled('from'), fn ($q) => $q->whereDate('created_at', '>=', $request->query('from')))
            ->when($request->filled('to'), fn ($q) => $q->whereDate('created_at', '<=', $request->query('to')))
            ->latest('id')
            ->paginate(25)
            ->withQueryString();

        $counts = HelpRequest::selectRaw('status, COUNT(*) as total')->groupBy('status')->pluck('total', 'status');
        $services = SubCategory::whereIn('id', HelpRequest::query()->select('sub_category_id'))->orderBy('name')->get(['id', 'name']);
        $staff = User::activeAdmins()->orderBy('name')->get(['id', 'name']);

        return view('admin.help-requests.index', compact('requests', 'filters', 'counts', 'services', 'staff'));
    }

    public function show(HelpRequest $helpRequest)
    {
        $helpRequest->load(['user', 'service', 'assignee', 'notes.user']);
        $staff = User::activeAdmins()->orderBy('name')->get(['id', 'name']);

        return view('admin.help-requests.show', ['help' => $helpRequest, 'staff' => $staff]);
    }

    public function update(Request $request, HelpRequest $helpRequest)
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(array_keys(HelpRequest::STATUSES))],
            'assigned_to' => ['nullable', 'integer', Rule::exists('users', 'id')->where('account_type', User::TYPE_ADMIN)->where('status', 1)],
        ]);

        $previousAssignee = $helpRequest->assigned_to;
        $helpRequest->update(['status' => $data['status'], 'assigned_to' => $data['assigned_to'] ?? null]);

        $changes = ['الحالة: ' . $helpRequest->statusLabel()];
        if ((int) $previousAssignee !== (int) $helpRequest->assigned_to) {
            $changes[] = 'المسؤول: ' . ($helpRequest->assignee?->name ?? 'بدون');
            if ($helpRequest->assignee && $helpRequest->assigned_to !== Auth::id()) {
                $helpRequest->assignee->notify(new HelpRequestNotification($helpRequest, assigned: true));
            }
        }
        $helpRequest->notes()->create(['user_id' => Auth::id(), 'body' => 'تحديث — ' . implode(' · ', $changes)]);

        return back()->with('success', 'تم تحديث الطلب');
    }

    public function addNote(Request $request, HelpRequest $helpRequest)
    {
        $data = $request->validate(['body' => 'required|string|max:3000'], ['body.required' => 'اكتب الملاحظة أولًا']);
        $helpRequest->notes()->create(['user_id' => Auth::id(), 'body' => $data['body']]);

        return back()->with('success', 'أُضيفت الملاحظة (داخلية، لا تظهر للعميل)');
    }
}
