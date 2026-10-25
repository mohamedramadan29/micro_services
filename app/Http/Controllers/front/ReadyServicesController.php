<?php

namespace App\Http\Controllers\front;

use App\Http\Controllers\Controller;
use App\Models\admin\Category;
use App\Models\admin\Service;
use Illuminate\Http\Request;

/** «خدمات جاهزة»: every published ready-made service from freelancers, with search, filters and sorts. */
class ReadyServicesController extends Controller
{
    public const SORTS = [
        'newest' => 'الأحدث',
        'popular' => 'الأكثر طلبًا',
        'price' => 'الأقل سعرًا',
        'price_desc' => 'الأعلى سعرًا',
    ];

    public function index(Request $request)
    {
        $sort = array_key_exists((string) $request->string('sort'), self::SORTS) ? (string) $request->string('sort') : 'newest';
        $group = $request->integer('group') ?: null;
        $service = $request->integer('service') ?: null;

        $query = Service::active()
            ->with(['user:id,name,user_name,image,professional_photo,rate,rating_count', 'subcategory:id,name,slug'])
            ->whereHas('user', fn ($u) => $u->where('status', 1)->where(fn ($b) => $b->whereNull('block_status')->orWhere('block_status', '!=', 0)))
            ->when($group, fn ($q) => $q->where('cat_id', $group))
            ->when($service, fn ($q) => $q->where('sub_cat_id', $service))
            ->when($request->filled('q'), function ($query) use ($request) {
                $term = '%' . str_replace(['%', '_'], ['\%', '\_'], (string) $request->string('q')) . '%';
                $query->where(fn ($q) => $q->where('name', 'like', $term)->orWhere('tags', 'like', $term)->orWhere('description', 'like', $term));
            })
            ->when(is_numeric($request->query('max_price')), fn ($q) => $q->whereRaw('CAST(price AS DECIMAL(12,2)) <= ?', [(float) $request->query('max_price')]));

        match ($sort) {
            'popular' => $query->orderByDesc('users_num_buy')->latest('id'),
            'price' => $query->orderByRaw('CAST(price AS DECIMAL(12,2)) ASC'),
            'price_desc' => $query->orderByRaw('CAST(price AS DECIMAL(12,2)) DESC'),
            default => $query->latest('id'),
        };

        $services = $query->paginate(18)->withQueryString();

        // Only groups and sub-services that have something to buy.
        $counts = Service::active()->selectRaw('sub_cat_id, COUNT(*) as total')->groupBy('sub_cat_id')->pluck('total', 'sub_cat_id');
        $groups = Category::ordered()->where('status', 1)
            ->with(['subCategories' => fn ($q) => $q->ordered()->where('status', 1)])
            ->get()
            ->map(function ($g) use ($counts) {
                $g->setRelation('subCategories', $g->subCategories->filter(fn ($s) => $counts->has($s->id))->values());

                return $g;
            })
            ->filter(fn ($g) => $g->subCategories->isNotEmpty())->values();

        return view('website.ready-services.index', compact('services', 'groups', 'counts', 'sort', 'group', 'service'));
    }
}
