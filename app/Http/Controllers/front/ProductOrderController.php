<?php

namespace App\Http\Controllers\front;

use App\Models\User;
use Illuminate\Http\Request;
use App\Models\admin\Product;
use App\Http\Traits\Message_Trait;
use App\Models\front\ProductOrder;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use Stripe\Stripe;
use Stripe\Checkout\Session;
use Stripe\Exception\ApiErrorException;

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
                'name' => 'required',
                'phone' => 'required',
                'email' => 'required|email',
                'product_id' => 'required',
            ];
            $messages = [
                'country.required' => ' من فضلك ادخل الدولة ',
                'city.required' => ' من فضلك ادخل المدينة ',
                'address.required' => ' من فضلك ادخل العنوان بشكل كامل ',
                'name.required' => ' من فضلك ادخل الاسم ',
                'phone.required' => ' من فضلك ادخل رقم الهاتف ',
                'email.required' => ' من فضلك ادخل البريد الالكتروني ',
                'email.email' => ' من فضلك ادخل بريد الكتروني بشكل صحيح ',
                'product_id.required' => 'يجب اختيار منتج للشراء',
            ];
            $validator = Validator::make($data, $rules, $messages);
            if ($validator->fails()) {
                return Redirect::back()->withInput()->withErrors($validator);
            }

            $product = Product::findOrFail($data['product_id']);
            $product_price = $product->discount ? $product->discount : $product->price;

            Stripe::setApiKey(env('STRIPE_SECRET'));

            $session = Session::create([
                'payment_method_types' => ['card', 'alipay'],
                'line_items' => [
                    [
                        'price_data' => [
                            'currency' => 'usd',
                            'product_data' => ['name' => $product->name],
                            'unit_amount' => $product_price * 100,
                        ],
                        'quantity' => 1,
                    ]
                ],
                'mode' => 'payment',
                'metadata' => [
                    'user_id' => Auth::id(),
                    'name' => $request->name,
                    'email' => $request->email,
                    'phone' => $request->phone,
                    'country' => $request->country,
                    'city' => $request->city,
                    'address' => $request->address,
                    'product_id' => $product->id,
                ],
                'success_url' => route('product.order.success') . '?session_id={CHECKOUT_SESSION_ID}&product_id=' . $product->id,
                'cancel_url' => route('product.order.cancel'),
            ]);

            return redirect($session->url);
        } catch (\Exception $e) {
            return $this->exception_message($e);
        }
    }


    public function paymentSuccess(Request $request)
    {

        try {
            Stripe::setApiKey(env('STRIPE_SECRET'));

            $session = Session::retrieve($request->session_id);
            if (!$session) {
                return Redirect::route('home')->withErrors(['حدث خطأ أثناء التحقق من الدفع.']);
            }

            $metadata = $session->metadata;
           // dd($metadata);
            $product = Product::findOrFail($metadata->product_id);
            $product_price = $product->discount ? $product->discount : $product->price;

            DB::beginTransaction();
            $order = new ProductOrder();
            $order->user_id = $metadata->user_id;
            $order->product_id = $metadata->product_id;
            $order->product_name = $product->name;
            $order->price = $product_price;
            $order->name = $metadata->name;
            $order->email = $metadata->email;
            $order->phone = $metadata->phone;
            $order->country = $metadata->country;
            $order->city = $metadata->city;
            $order->address = $metadata->address;
            $order->order_status = 'تم الدفع';
            $order->save();
            DB::commit();

            return $this->success_message('تمت عملية الشراء بنجاح.');
        } catch (ApiErrorException $e) {
            return $this->exception_message($e);
        }
    }
    public function PaymentCancel()
    {
        return $this->Error_message('تم الغاء الدفع');
    }
}
