<?php

namespace App\Http\Controllers\front;

use App\Http\Controllers\Controller;
use App\Models\admin\Category;
use App\Models\front\UserPortfolio;
use App\Models\User;
use Illuminate\Http\Request;

/** «أعمال المستقلين»: published works (reviewed by the team) of approved freelancers. */
class PortfolioController extends Controller
{
    public const SORTS = ['newest' => 'الأحدث', 'oldest' => 'الأقدم'];

    public function index(Request $request)
    {
        $sort = array_key_exists((string) $request->string('sort'), self::SORTS) ? (string) $request->string('sort') : 'newest';
        $group = $request->integer('group') ?: null;
        $listed = User::listedFreelancers()->select('id');

        $base = UserPortfolio::published()->whereIn('user_id', $listed);
        $works = (clone $base)
            ->with(['Category:id,name', 'User:id,name,user_name,image,professional_photo,rate,rating_count,job_title'])
            ->when($group, fn ($q) => $q->where('category_id', $group))
            ->when($request->filled('q'), function ($query) use ($request) {
                $term = '%' . str_replace(['%', '_'], ['\%', '\_'], (string) $request->string('q')) . '%';
                $query->where(fn ($q) => $q->where('title', 'like', $term)->orWhere('description', 'like', $term)
                    ->orWhereHas('User', fn ($u) => $u->where('name', 'like', $term)));
            })
            ->when($sort === 'oldest', fn ($q) => $q->oldest('id'), fn ($q) => $q->latest('id'))
            ->paginate(18)->withQueryString();

        $counts = (clone $base)->selectRaw('category_id, COUNT(*) as total')->groupBy('category_id')->pluck('total', 'category_id');
        $groups = Category::ordered()->where('status', 1)->whereIn('id', $counts->keys())->get(['id', 'name']);

        return view('website.portfolio.index', compact('works', 'groups', 'counts', 'group', 'sort'));
    }

    public function details($id, $slug)
    {
        $work = UserPortfolio::with(['Category', 'User'])->findOrFail($id);
        // Only published works are public; the owner and the team can still open his own.
        $viewer = auth()->user();
        abort_unless($work->isPublished() || ($viewer && ((int) $viewer->id === (int) $work->user_id || $viewer->account_type === \App\Models\User::TYPE_ADMIN)), 404);
        return view('website.portfolio.details', compact('work'));
    }
}
