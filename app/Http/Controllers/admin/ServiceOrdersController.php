<?php

namespace App\Http\Controllers\admin;

use App\Models\front\Order;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ServiceOrdersController extends Controller
{
    public function index()
    {
        $orders = Order::latest()->paginate(30);
        return view('admin.service-orders.index', compact('orders'));
    }
}
