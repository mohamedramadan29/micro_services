<?php

namespace App\Http\Controllers;

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
        return view('website.services');
    }

    public function service_details()
    {
        return view('website.service-details');
    }
    public function categories()
    {
        return view('website.categories');
    }


}
