<?php

namespace App\Http\Controllers\front;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Traits\Message_Trait;
use App\Models\front\ProductOrder;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;

class ProductOrderController extends Controller
{
    use Message_Trait;

    public function store(Request $request)
    {
        try {
            $data = $request->all();
            $rules = [
                'country' => 'required',
                'city' => 'required',
                'address' => 'required',
            ];
            $messages = [
                'country.required' => ' من فضلك ادخل الدولة ',
                'city.required' => ' من فضلك ادخلا المدينة ',
                'address.required' => ' من فضلك ادخل العنوان بشكل كامل ',
            ];
            $validator = Validator::make($data,$rules,$messages);
            if ($validator->fails()){
                return Redirect::back()->withInput()->withErrors($validator);
            }
            $user = User::where('id', Auth::id())->first();
            if ($user->balance < $data['product_price']) {
                return Redirect::back()->withErrors([' رصيدك الحالي لا يكفي للطلب  ']);
            }
            DB::beginTransaction();
            $order = new ProductOrder();
            $order->user_id = Auth::id();
            $order->product_id = $data['product_id'];
            $order->product_name = $data['product_name'];
            $order->price = $data['product_price'];
            $order->name = Auth::user()->name;
            $order->email = Auth::user()->email;
            $order->country = $data['country'];
            $order->city = $data['city'];
            $order->address = $data['address'];
            $order->order_status =  ' لم يبدا  ';
            $order->save();
            ////////////// Send Notification To Admin
            ///

            ///////// Decrease USer Price
            $user->balance -= $data['product_price'];
            $user->save();
            DB::commit();
            return $this->success_message( ' تم اضافة الطلب الخاص بك بنجاح  ');
        } catch (\Exception $e) {
            return $this->exception_message($e);
        }
    }
}
