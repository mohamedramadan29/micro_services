<?php

namespace App\Http\Controllers\front;

use App\Http\Controllers\Controller;
use App\Models\admin\Blog;
use App\Models\admin\BlogCategory;
use Illuminate\Http\Request;

class BlogController extends Controller
{
    public function Categories(){
        $categories = BlogCategory::active()->latest()->get();
        return view('website.blog.categories',compact('categories'));
    }

    public function categoryDetails($slug){
        $category = BlogCategory::with('blogs')->where('slug',$slug)->first();
        return view('website.blog.category-details',compact('category'));
    }

    public function blogDetails($slug){
        $blog = Blog::where('slug',$slug)->first();
        if(!$blog){
            abort(404);
        }
        return view('website.blog.blog',compact('blog'));
    }
}
