<?php

namespace App\Http\Controllers\front;

use Illuminate\Http\Request;
use App\Models\admin\Product;
use App\Http\Controllers\Controller;

class ProductController extends Controller
{
    public const SORTS = ['newest' => 'الأحدث', 'price' => 'الأقل سعرًا', 'price_desc' => 'الأعلى سعرًا'];

    public function index(Request $request)
    {
        $sort = array_key_exists((string) $request->string('sort'), self::SORTS) ? (string) $request->string('sort') : 'newest';
        // `search` is the old query key (old links and the navbar search).
        $term = trim((string) $request->string($request->filled('q') ? 'q' : 'search'));
        // Effective price = discount when set, else price.
        $effective = 'CASE WHEN discount > 0 THEN discount ELSE price END';

        $products = Product::where('status', 1)
            ->when($term !== '', function ($query) use ($term) {
                $like = '%' . str_replace(['%', '_'], ['\%', '\_'], $term) . '%';
                $query->where(fn ($q) => $q->where('name', 'like', $like)->orWhere('short_description', 'like', $like));
            })
            ->when($sort === 'price', fn ($q) => $q->orderByRaw($effective . ' asc'))
            ->when($sort === 'price_desc', fn ($q) => $q->orderByRaw($effective . ' desc'))
            ->orderBy('id', 'desc')
            ->paginate(12)->withQueryString();

        return view('website.products', compact('products', 'sort', 'term'));
    }

    public function product_details($slug)
    {
        $product = Product::with('gallary')->where('slug', $slug)->firstOrFail();
        abort_unless((int) $product->status === 1 || auth()->user()?->account_type === \App\Models\User::TYPE_ADMIN, 404);

        $related = Product::where('status', 1)->where('id', '!=', $product->id)->latest('id')->take(3)->get();

        return view('website.product_details', compact('product', 'related'));
    }
}
