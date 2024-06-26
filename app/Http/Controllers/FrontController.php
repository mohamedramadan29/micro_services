<?php

namespace App\Http\Controllers;

use App\Models\admin\Category;
use App\Models\admin\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FrontController extends Controller
{

    public function index()
    {
        return view('website.index');
    }

    public function services()
    {
        $services = Service::with('category', 'user')->where('status', '1')->paginate(12);
        $categories = Category::with('parents')->withCount('services')->where('status', '1')->get();
        //$categories = Category::with(['children', 'children.services'])->withCount('services')->where('status', '1')->get();
        //dd($categories);
        return view('website.services', compact('services', 'categories'));
    }

    public function service_details($id, $slug)
    {
        $service = Service::with('user')->where('id', $id)->first();
        $more_servicess = Service::where('cat_id', $service['cat_id'])->where('id', '!=', $service['id'])->OrderBy('id')->limit('6')->get();
        return view('website.service-details', compact('service', 'more_servicess'));
    }

    public function categories()
    {
        $categories = Category::with('parents')->where('parent_id', '0')->paginate(12);
      //  dd($categories);
        return view('website.categories', compact('categories'));
    }

    public function sub_categories($slug)
    {
        $category = Category::where('slug', $slug)->first();
        $category_id = $category['id'];
        if ($category) {
            $sub_categories = Category::where('parent_id', $category_id)->paginate(12);
            return view('website.sub-categories', compact('category', 'sub_categories'));
        } else {
            abort(404);
        }
    }

    public function category_services($slug)
    {
        $category = Category::where('slug', $slug)->first();
        $category_id = $category['id'];
        if ($category) {
            $services = Service::where('cat_id', $category_id)->paginate(12);
            return view('website.category-services', compact('services','category'));
        } else {
            abort(404);
        }
    }
}
