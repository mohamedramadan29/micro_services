<?php

namespace App\Http\Controllers;

use App\Http\Traits\Message_Trait;
use App\Models\Cart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class CartController extends Controller
{
    use Message_Trait;

    public function cart()
    {
        $items = Cart::getCartItems();
        $count_items = count(Cart::getCartItems());
        //dd($items);
        return view('website.cart', compact('items', 'count_items'));
    }

    ///////// Add To Cart
    public function AddToCart(Request $request)
    {
        $cartData = $request->all();
        // dd($cartData);

//        dd($cartData);
        // Generate Session Id If Not Exists
        $session_id = Session::get('session_id');
        if (empty($session_id)) {
            $session_id = Session::getId();
            Session::put('session_id', $session_id);
        }
        //Check If This Services Already Exist Or Not
        if (Auth::check()) {
            // User Is Login
            $user_id = Auth::user()->id;
            $countServices = Cart::where(['service_id' => $cartData['service_id'], 'user_id' => $user_id])->count();
        } else {
            // User Not Login
            $user_id = 0;
            $countServices = Cart::where(['service_id' => $cartData['service_id'], 'session_id' => $session_id])->count();
        }
        if ($countServices > 0) {
            return redirect()->back()->withErrors('تم اضافه الخدمة من قبل الي السله ');
        }
        // Save Product In Cart Tabel
        $item = new Cart();
        $item->session_id = $session_id;
        $item->user_id = $user_id;
        $item->service_id = $cartData['service_id'];
        $item->user_serv = $cartData['user_serv'];
        $item->quantity = $cartData['qty'];
        $item->save();
        return $this->success_message(' تم اضافه الخدمة الي السله');
    }
    public function deleteCart(Request $request)
    {
        $alldata = $request->all();
        //dd($alldata);
        Cart::where('id', $alldata['cartId'])->delete();
        return $this->success_message(' تم حذف الخدمة من السلة  ');
    }

}
