<?php

namespace App\Http\Controllers\front;

use App\Http\Controllers\Controller;
use App\Http\Traits\Message_Trait;
use App\Models\admin\Setting;
use App\Models\front\Cart;
use App\Models\front\Order;
use App\Models\front\OrderDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;

class CheckOutController extends Controller
{
    use Message_Trait;

    public function index()
    {
        $items = Cart::getCartItems();

        $count_items = count(Cart::getCartItems());
        if ($count_items > 0) {
            return view('website.checkout');
        }

    }

    public function order(Request $request)
    {

        // Get The Public Setting To GEt Commision
        $public_setting = Setting::first();
        $website_commission = $public_setting['website_commission'] / 100;
        try {
            $data = $request->all();
            $items = Cart::getCartItems();

            $count_items = count(Cart::getCartItems());
            if ($count_items > 0) {
                ///////////// Insert Order
                ///
                // Get The Cart Items Price
                $total_price_in_cart = 0;
                foreach ($items as $item) {
                    $total_price_in_cart = $total_price_in_cart + $item['price'] * $item['quantity'];
                }

                $order = new Order();
                $count_orders = Order::count();
                if ($count_orders > 0) {
                    $last_order = Order::orderBy('order_number', 'desc')->first();
                    $new_order_number = $last_order->order_number + 1;
                } else {
                    $new_order_number = 1;
                }
                /////////////// Stop Here Now In Make Orders
                DB::beginTransaction();
                $order->order_number = $new_order_number;
                $order->user_buyer = Auth::id();
                $order->order_price = $total_price_in_cart;
                $order->website_commission = $total_price_in_cart * $website_commission;
                $order->save();
                ////////// Insert Order Details
                /// In Order Details Table
                foreach ($items as $item) {
                    $item_total_price = $item['price'] * $item['quantity'];
                    $orderDetails = new OrderDetail();
                    $orderDetails->order_id = $order->id;
                    $orderDetails->order_number = $new_order_number;
                    $orderDetails->user_seller = $item['user_serv'];
                    $orderDetails->user_buyer = $item['user_id'];
                    $orderDetails->service_id = $item['service_id'];
                    $orderDetails->service_name = $item['service_name'];
                    $orderDetails->service_price = $item['price'];
                    $orderDetails->quantity = $item['quantity'];
                    $orderDetails->website_commission = $item_total_price * $website_commission;
                    $orderDetails->seller_commission = $item_total_price - $orderDetails->website_commission;
                    $orderDetails->save();
                }


                DB::commit();
                /////// Delte The Cart Items And Got Purches Section
                ///
                Cart::clear();
                return \redirect()->to('purches');
            }
        } catch (\Exception $e) {
            return $this->exception_message($e);
        }
    }
}
