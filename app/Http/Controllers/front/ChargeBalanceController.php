<?php

namespace App\Http\Controllers\front;

use App\Http\Controllers\Controller;
use App\Http\Traits\Message_Trait;
use App\Models\OnlinePayment;
use App\Services\Payments\OnlinePaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChargeBalanceController extends Controller
{
    use Message_Trait;

    /** Top up the balance through EasyKash; it is credited once EasyKash confirms the payment. */
    public function charge_balance(Request $request, OnlinePaymentService $payments)
    {
        $data = $request->validate([
            'price' => 'required|numeric|min:1|max:100000',
        ], [
            'price.required' => 'الرجاء ادخال المبلغ',
            'price.numeric' => 'الرجاء ادخال المبلغ بشكل صحيح',
            'price.min' => 'الرجاء ادخال المبلغ بشكل صحيح اقل مبلغ مسموح به 1',
            'price.max' => 'أقصى مبلغ للشحن مرة واحدة 100000',
        ]);

        try {
            return redirect()->away($payments->start(OnlinePayment::PURPOSE_BALANCE, Auth::user(), round((float) $data['price'], 2),
                'شحن رصيد للمستخدم ' . Auth::user()->name, ['back_url' => route('user_balance')]));
        } catch (\Throwable $e) {
            return $this->payment_error($e);
        }
    }
}
