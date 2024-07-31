<?php

namespace App\Http\Controllers\front;

use App\Http\Controllers\Controller;
use App\Http\Traits\Message_Trait;
use App\Models\front\Cart;
use App\Models\front\Order;
use App\Models\front\OrderDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;

class CheckOutController extends Controller
{
    use Message_Trait;

    public function index()
    {
        $items = Cart::getCartItems();
        $count_items = count(Cart::getCartItems());
        if ($count_items > 0) {
            ///////////// Insert Order
            ///
            $order = new Order();
            $count_orders = Order::count();

            if ($count_orders > 0) {
                $last_order = Order::orderBy('order_number', 'desc')->first();
                $new_order_number = $last_order->order_number + 1;
            } else {
                $new_order_number = 1;
            }

            /////////////// Stop Here Now In Make Orders
            $order->order_number = $new_order_number;
            $order->user_seller = ;
            $order->user_buyer = '';
            $order->order_price = '';
            $order->website_commission = '';
            $order->seller_commission = '';
            ////////// Insert Order Details
            /// In Order Details Table
            $orderDetails = new OrderDetail();
            foreach ($items as $item) {
                $orderDetails->order_id = '';
                $orderDetails->order_number = '';
                $orderDetails->user_seller = '';
                $orderDetails->user_buyer = '';
                $orderDetails->service_id = '';
                $orderDetails->service_name = '';
                $orderDetails->service_price = '';

            }
        }
        return view('website.checkout');
    }

    public function order(Request $request)
    {
        try {
            $data = $request->all();


        } catch (\Exception $e) {
            return $this->exception_message($e);
        }
    }
}
