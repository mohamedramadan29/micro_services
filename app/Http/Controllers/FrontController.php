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
        $services = Service::with('category','user')->where('status','1')->paginate(12);
       $categories = Category::with('parents')->withCount('services')->where('status','1')->get();
        //$categories = Category::with(['children', 'children.services'])->withCount('services')->where('status', '1')->get();
        //dd($categories);
        return view('website.services',compact('services','categories'));
    }

    public function service_details($id,$slug)
    {
        $service = Service::with('user')->where('id',$id)->first();
        $more_servicess = Service::where('cat_id',$service['cat_id'])->where('id','!=',$service['id'])->OrderBy('id')->limit('6')->get();
        return view('website.service-details',compact('service','more_servicess'));
    }
    public function categories()
    {
        return view('website.categories');
    }


}
