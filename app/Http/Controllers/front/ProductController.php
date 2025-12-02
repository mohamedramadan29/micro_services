<?php

namespace App\Http\Controllers\front;

use Illuminate\Http\Request;
use App\Models\admin\Product;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::where('status', 1);
        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->input('search') . '%');
        }
        $products = $query->orderBy('id', 'desc')->paginate(6);
        return view('website.products', compact('products'));
    }
    public function product_details($slug)
    {
        $product = Product::where('slug', $slug)->first();
        //  dd($product);
        return view('website.product_details', compact('product'));
    }
}
