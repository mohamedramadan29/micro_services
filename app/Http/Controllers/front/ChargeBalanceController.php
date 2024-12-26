<?php

namespace App\Http\Controllers\front;

use Stripe\Charge;
use Stripe\Stripe;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Traits\Message_Trait;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\front\PaymentTransaction;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;

class ChargeBalanceController extends Controller
{
    use Message_Trait;
    // public function charge_balance(Request $request)
    // {
    //     try {
    //         $data = $request->all();
    //         $price = $data['price'];
    //         $user = User::where('id', Auth::user()->id)->first();
    //         $old_balance = $user->balance;
    //         $new_balance = $old_balance + $price;
    //         $user->balance = $new_balance;
    //         $user->save();
    //         return $this->success_message('تم اضافة الرصيد الخاص بك بنجاح ');
    //     } catch (\Exception $e) {
    //         return $this->exception_message($e);
    //     }
    // }

    public function charge_balance(Request $request)
    {
        $data = $request->all();
        $price = $data['price'];
        $rules = [
            'price' => 'required|numeric|min:1',
        ];
        $messages = [
            'price.required' => 'الرجاء ادخال المبلغ',
            'price.numeric' => 'الرجاء ادخال المبلغ بشكل صحيح',
            'price.min' => 'الرجاء ادخال المبلغ بشكل صحيح اقل مبلغ مسموح به 1',
        ];
        $validator = Validator::make($data, $rules, $messages);
        if ($validator->fails()) {
            return Redirect::back()->withErrors($validator)->withInput();
        }
        // عمولة Stripe
        $fee_percentage = 2.9 / 100;
        $fixed_fee = 0.30;
        $commission = ($price * $fee_percentage) + $fixed_fee; // حساب العمولة
        $net_amount = $price - $commission; // المبلغ الصافي بعد خصم العمولة
        Stripe::setApiKey(env('STRIPE_SECRET'));

        try {
            DB::beginTransaction();
            // إجراء المعاملة
            $charge = Charge::create([
                'amount' => $request->price * 100, // تحويل المبلغ إلى السنت
                'currency' => 'usd',
                'source' => $request->stripeToken,
                'description' => 'شحن رصيد للمستخدم ' . Auth::user()->name,
            ]);

            // تخزين تفاصيل المعاملة
            PaymentTransaction::create([
                'transaction_id' => $charge->id,
                'payment_method' => 'Stripe',
                'payment_status' => $charge->status,
                'payment_amount' => $request->price,
                'payment_fee' => $commission,
                'payment_currency' => $charge->currency,
                'payment_description' => $charge->description,
                'payment_response' => json_encode($charge), // تخزين الرد كـ JSON
                'user_id' => Auth::id(),
            ]);
            // تحديث رصيد المستخدم
            $user = User::where('id', Auth::user()->id)->first();
            $user->balance += $net_amount; // تحديث الرصيد
            $user->save(); // حفظ التحديث
            DB::commit();
            return $this->success_message('تم اضافة الرصيد الخاص بك بنجاح ');
        } catch (\Exception $e) {
            DB::rollBack(); // التراجع عن العملية في حالة حدوث خطأ
            return $this->exception_message($e);

        }

    }
}
