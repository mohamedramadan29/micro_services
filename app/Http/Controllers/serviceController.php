<?php

namespace App\Http\Controllers;

use App\Models\admin\Category;
use App\Models\admin\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class serviceController extends Controller
{
    public function index()
    {
        $services = Service::where('user_id',Auth::id())->get();
        return view('website.service.index',compact('services'));
    }
    public function add()
    {
        $categories = Category::where('status', '1')->get();
        return view('website.service.add',compact('categories'));
    }
}
