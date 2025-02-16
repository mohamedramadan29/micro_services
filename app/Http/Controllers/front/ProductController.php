<?php

namespace App\Http\Controllers\front;

use Illuminate\Http\Request;
use App\Models\admin\Product;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::paginate(6);
        return view('website.products', compact('products'));
    }
    public function product_details($slug)
    {
        $product = Product::where('slug', $slug)->first();
        //  dd($product);

        return view('website.product_details', compact('product'));
    }
}
