<?php

namespace App\Http\Controllers\front;

use App\Http\Controllers\Controller;
use App\Http\Traits\Message_Trait;
use App\Http\Traits\Slug_Trait;
use App\Models\admin\Service;
use App\Models\admin\Setting;
use App\Models\front\Cart;
use App\Models\front\Order;
use App\Models\front\OrderDetail;
use App\Models\User;
use App\Notifications\NewOrderNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Redirect;

class CheckOutController extends Controller
{
    use Message_Trait;
    use Slug_Trait;

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
                    $user = User::find($item['user_serv']);
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
                    /////// Send Notification To Seller New Order
                    ///
                    Notification::send($user, new NewOrderNotification(Auth::id(), Auth::user()->name, Auth::user()->user_name, $item['service_id'], $this->CustomeSlug($item['service_name']), $item['service_name']));
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

    ////////////////////////// Start Create Order /////////////////
    ///
    public function create_order(Request $request)
    {
        // Get The Public Setting To GEt Commision
        $public_setting = Setting::first();
        $website_commission = $public_setting['website_commission'] / 100;
        try {
            $data = $request->all();
            $service = Service::findOrFail($data['service_id']);
            $service_price = $service['price'];
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
            $order->user_seller = $service['user_id'];
            $order->order_price = $service_price;
            $order->website_commission = $service_price * $website_commission;
            $order->seller_commission = $service_price - ($service_price * $website_commission);
            $order->save();
            /////// Send Notification To Seller New Order
            $user = User::find($service['user_id']);
            Notification::send($user, new NewOrderNotification(Auth::id(), Auth::user()->name, Auth::user()->user_name, $service['id'], $this->CustomeSlug($service['name']), $service['name']));
            DB::commit();
            return $this->success_message(' تم طلب الخدمة بنجاح  ');
        } catch (\Exception $e) {
            return $this->exception_message($e);
        }
    }
}
