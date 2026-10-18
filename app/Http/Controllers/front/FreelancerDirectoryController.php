<?php

namespace App\Http\Controllers\front;

use App\Http\Controllers\Controller;
use App\Models\admin\Category;
use App\Models\User;
use Illuminate\Http\Request;

/** «المستقلين»: only freelancers the admin approved (client file «تعديلات», item 03). */
class FreelancerDirectoryController extends Controller
{
    public const SORTS = [
        'featured' => 'الأنسب',
        'rating' => 'الأعلى تقييمًا',
        'price' => 'الأقل سعرًا',
        'newest' => 'الأحدث',
    ];

    public function index(Request $request)
    {
        $filters = $request->only(['q', 'group', 'service', 'rating', 'max_price', 'availability', 'language', 'sort']);
        $sort = array_key_exists($filters['sort'] ?? null, self::SORTS) ? $filters['sort'] : 'featured';

        $query = User::listedFreelancers()
            ->with(['specializations' => fn ($q) => $q->orderBy('name')])
            ->when($request->filled('q'), function ($query) use ($request) {
                $term = '%' . str_replace(['%', '_'], ['\%', '\_'], $request->query('q')) . '%';
                $query->where(fn ($q) => $q->where('name', 'like', $term)->orWhere('bio', 'like', $term)->orWhere('job_title', 'like', $term)
                    ->orWhereHas('specializations', fn ($s) => $s->where('name', 'like', $term)));
            })
            ->when($request->integer('group'), fn ($q, $group) => $q->whereHas('specializations', fn ($s) => $s->where('parent_id', $group)))
            ->when($request->integer('service'), fn ($q, $service) => $q->whereHas('specializations', fn ($s) => $s->where('sub_categories.id', $service)))
            ->when($request->integer('rating'), fn ($q, $rating) => $q->where('rate', '>=', $rating))
            ->when($request->filled('max_price') && is_numeric($request->query('max_price')), fn ($q) => $q->whereNotNull('starting_price')->where('starting_price', '<=', (float) $request->query('max_price')))
            ->when(array_key_exists($request->query('availability'), User::AVAILABILITY), fn ($q) => $q->where('availability', $request->query('availability')))
            ->when($request->filled('language'), fn ($q) => $q->where('languages', 'like', '%' . str_replace(['%', '_'], ['\%', '\_'], $request->query('language')) . '%'));

        match ($sort) {
            'rating' => $query->orderByDesc('rate'),
            'price' => $query->orderByRaw('starting_price IS NULL')->orderBy('starting_price'),
            'newest' => $query->orderByDesc('freelancer_reviewed_at'),
            default => $query->orderByDesc('is_featured')->orderByDesc('rate')->orderByDesc('freelancer_reviewed_at'),
        };

        $freelancers = $query->paginate(18)->withQueryString();
        $featured = User::listedFreelancers()->where('is_featured', true)->with('specializations')->inRandomOrder()->limit(10)->get();
        $groups = Category::ordered()->where('status', 1)
            ->with(['subCategories' => fn ($q) => $q->ordered()->where('status', 1)])
            ->get()->filter(fn ($g) => $g->subCategories->isNotEmpty())->values();

        return view('website.freelancers.index', compact('freelancers', 'featured', 'groups', 'filters', 'sort'));
    }
}
