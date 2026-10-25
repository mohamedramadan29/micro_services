<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\front\UserPortfolio;
use App\Notifications\RequestUpdateNotification;
use App\Support\Msg;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

/** Freelancers' works («معرض أعمالي») are published on the site only after the team reviews them. */
class FreelancerWorkController extends Controller
{
    public const TABS = [
        'pending' => UserPortfolio::STATUS_PENDING,
        'published' => UserPortfolio::STATUS_PUBLISHED,
        'rejected' => UserPortfolio::STATUS_REJECTED,
    ];

    public function index(Request $request)
    {
        $tab = array_key_exists((string) $request->string('tab'), self::TABS) ? (string) $request->string('tab') : 'pending';
        $works = UserPortfolio::with(['User:id,name,email,user_name,freelancer_status', 'Category:id,name'])
            ->where('status', self::TABS[$tab])
            ->when($request->filled('q'), function ($query) use ($request) {
                $term = '%' . str_replace(['%', '_'], ['\%', '\_'], (string) $request->string('q')) . '%';
                $query->where(fn ($q) => $q->where('title', 'like', $term)
                    ->orWhereHas('User', fn ($u) => $u->where('name', 'like', $term)->orWhere('email', 'like', $term)));
            })
            ->when($tab === 'pending', fn ($q) => $q->oldest('updated_at'), fn ($q) => $q->latest('updated_at'))
            ->paginate(20)
            ->withQueryString();
        $counts = UserPortfolio::selectRaw('status, COUNT(*) as total')->groupBy('status')->pluck('total', 'status');

        return view('admin.freelancer-works.index', compact('works', 'tab', 'counts'));
    }

    public function publish(UserPortfolio $work)
    {
        $this->decide($work, UserPortfolio::STATUS_PUBLISHED, null);

        return back()->with('success', 'تم نشر العمل وإبلاغ المستقل.');
    }

    public function reject(Request $request, UserPortfolio $work)
    {
        $data = $request->validate(['note' => 'required|string|max:500'], [
            'note.required' => 'اكتب سبب عدم القبول ليعرف المستقل ما يعدّله',
            'note.max' => 'السبب طويل جدًا (500 حرف كحد أقصى)',
        ]);
        $this->decide($work, UserPortfolio::STATUS_REJECTED, $data['note']);

        return back()->with('success', 'تم تسجيل عدم القبول وإبلاغ المستقل.');
    }

    private function decide(UserPortfolio $work, int $status, ?string $note): void
    {
        $work->forceFill([
            'status' => $status,
            'review_note' => $note,
            'reviewed_at' => now(),
            'reviewed_by' => Auth::id(),
        ])->save();
        Cache::forget('admin-menu-badges');

        $work->User?->notify(new RequestUpdateNotification(
            $status === UserPortfolio::STATUS_PUBLISHED
                ? Msg::t('requests.portfolio_published', ['title' => $work->title])
                : Msg::t('requests.portfolio_rejected', ['title' => $work->title, 'reason' => $note]),
            '/portfolio/user/index',
            'request_updates'
        ));
    }
}
