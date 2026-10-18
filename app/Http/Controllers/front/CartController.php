<?php

namespace App\Http\Controllers\front;

use App\Http\Controllers\Controller;
use App\Http\Traits\Message_Trait;
use App\Models\admin\Service;
use App\Models\front\Cart;
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
        // Name, price and seller always come from the service itself, never from the form.
        $service = Service::where('id', $cartData['service_id'] ?? 0)->firstOrFail();
        if (Auth::check() && (int) $service->user_id === (int) Auth::id()) {
            return redirect()->back()->withErrors('لا يمكنك اضافة خدمتك الخاصة الي السله');
        }
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
        $item->service_id = $service->id;
        $item->service_name = $service->name;
        $item->price = $service->price;
        $item->user_serv = $service->user_id;
        $item->quantity = 1;
        $item->save();
        app(\App\Services\Analytics\Tracker::class)->record('cart_add', ['sub_category_id' => $service->sub_cat_id]);
        return $this->success_message(' تم اضافه الخدمة الي السله');
    }
    public function deleteCart(Request $request)
    {
        // Only rows of this visitor's own cart.
        Cart::where('id', $request->input('cartId'))
            ->where(fn ($q) => Auth::check()
                ? $q->where('user_id', Auth::id())
                : $q->where('user_id', 0)->where('session_id', Session::get('session_id')))
            ->delete();
        return $this->success_message(' تم حذف الخدمة من السلة  ');
    }

}
