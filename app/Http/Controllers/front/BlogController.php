<?php

namespace App\Http\Controllers\front;

use App\Http\Controllers\Controller;
use App\Models\admin\Blog;
use App\Models\admin\BlogCategory;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Blog organised by section (client file «تعديلات», item 04): one index with
 * search, section chips and paginated cards; article pages with related
 * articles. Drafts and scheduled articles are never public.
 */
class BlogController extends Controller
{
    public function Categories(Request $request)
    {
        $categories = BlogCategory::active()->whereHas('blogs', fn ($q) => $q->published())->orderBy('name')->get();
        $category = $request->filled('category') ? $categories->firstWhere('slug', $request->query('category')) : null;

        $blogs = Blog::published()
            ->with(['Category', 'Author'])
            ->when($category, fn ($q) => $q->where('category_id', $category->id))
            ->when($request->filled('q'), function ($query) use ($request) {
                $term = '%' . str_replace(['%', '_'], ['\%', '\_'], $request->query('q')) . '%';
                $query->where(fn ($q) => $q->where('name', 'like', $term)->orWhere('short_desc', 'like', $term)->orWhere('desc', 'like', $term)->orWhere('meta_keywords', 'like', $term));
            })
            ->orderByDesc('publish_date')->orderByDesc('id')
            ->paginate(12)
            ->withQueryString();

        return view('website.blog.index', compact('blogs', 'categories', 'category'));
    }

    /** Old section links keep working. */
    public function categoryDetails($slug)
    {
        BlogCategory::active()->where('slug', $slug)->firstOrFail();

        return redirect()->route('blogCategories', ['category' => $slug], 301);
    }

    public function blogDetails($slug)
    {
        $blog = Blog::with(['Category', 'Author'])->where('slug', $slug)->firstOrFail();
        $isAdmin = Auth::user()?->account_type === User::TYPE_ADMIN;
        // A draft or scheduled article is only previewed by admins.
        abort_unless($blog->isPublished() || $isAdmin, 404);

        $related = Blog::published()->with('Category')->whereKeyNot($blog->id)
            ->when($blog->category_id, fn ($q) => $q->where('category_id', $blog->category_id))
            ->orderByDesc('publish_date')->limit(3)->get();
        if ($related->count() < 3) {
            $related = $related->merge(Blog::published()->with('Category')->whereKeyNot($blog->id)->whereNotIn('id', $related->pluck('id'))
                ->orderByDesc('publish_date')->limit(3 - $related->count())->get());
        }

        return view('website.blog.blog', compact('blog', 'related'));
    }
}
