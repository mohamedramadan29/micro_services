<?php

namespace App\Http\Controllers\front;

use App\Http\Controllers\Controller;
use App\Models\admin\Category;
use App\Models\admin\nafizhaPortfolio;
use Illuminate\Http\Request;

class NafizhaPortfolioController extends Controller
{
    public const SORTS = PortfolioController::SORTS;

    public function index(Request $request)
    {
        $sort = array_key_exists((string) $request->string('sort'), self::SORTS) ? (string) $request->string('sort') : 'newest';
        $group = $request->integer('group') ?: null;

        // Status 1 = active; archived works (0) are never public.
        $base = nafizhaPortfolio::where('status', 1);
        $works = (clone $base)
            ->with('Category:id,name')
            ->when($group, fn ($q) => $q->where('category_id', $group))
            ->when($request->filled('q'), function ($query) use ($request) {
                $term = '%' . str_replace(['%', '_'], ['\%', '\_'], (string) $request->string('q')) . '%';
                $query->where(fn ($q) => $q->where('title', 'like', $term)->orWhere('description', 'like', $term));
            })
            ->when($sort === 'oldest', fn ($q) => $q->oldest('id'), fn ($q) => $q->latest('id'))
            ->paginate(18)->withQueryString();

        $counts = (clone $base)->selectRaw('category_id, COUNT(*) as total')->groupBy('category_id')->pluck('total', 'category_id');
        $groups = Category::ordered()->where('status', 1)->whereIn('id', $counts->keys())->get(['id', 'name']);

        return view('website.portfolio-nafizha.index', compact('works', 'groups', 'counts', 'group', 'sort'));
    }

    public function details($id, $slug)
    {
        $work = nafizhaPortfolio::with('Category')->findOrFail($id);
        abort_unless((int) $work->status === 1 || auth()->user()?->account_type === \App\Models\User::TYPE_ADMIN, 404);

        $related = nafizhaPortfolio::where('status', 1)->where('id', '!=', $work->id)
            ->when($work->category_id, fn ($q) => $q->where('category_id', $work->category_id))
            ->latest('id')->take(3)->get();

        return view('website.portfolio-nafizha.details', compact('work', 'related'));
    }
}
