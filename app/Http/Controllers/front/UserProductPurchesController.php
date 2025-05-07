<?php

namespace App\Http\Controllers\front;

use App\Http\Controllers\Controller;
use App\Models\front\ProductOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserProductPurchesController extends Controller
{
    public function index(){
        $purches = ProductOrder::where('user_id', Auth::user()->id)->get();
        return view('website.user.product_purches', compact('purches'));
    }
}
