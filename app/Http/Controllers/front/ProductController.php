<?php

namespace App\Http\Controllers\front;

use App\Http\Controllers\Controller;
use App\Models\admin\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::where('status',1)->paginate(6);
        return view('website.products',compact('products'));
    }
    public function product_details($slug)
    {
        $product = Product::where('slug',$slug)->first();
      //  dd($product);
        return view('website.product_details',compact('product'));
    }
}
