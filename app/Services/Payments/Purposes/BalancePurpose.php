<?php

namespace App\Services\Payments\Purposes;

use App\Models\front\PaymentTransaction;
use App\Models\OnlinePayment;
use App\Models\User;

/** Topping up the user's site balance. */
class BalancePurpose extends SitePurpose
{
    public function paid(OnlinePayment $payment): void
    {
        PaymentTransaction::create([
            'transaction_id' => $payment->gateway_transaction ?? (string) $payment->id,
            'payment_method' => 'EasyKash',
            'payment_status' => 'paid',
            'payment_amount' => $payment->amount,
            'payment_fee' => 0,
            'payment_currency' => $payment->currency,
            'payment_description' => $payment->description,
            'payment_response' => json_encode(['online_payment_id' => $payment->id, 'paid_amount' => $payment->paid_amount]),
            'user_id' => $payment->user_id,
        ]);

        $user = User::whereKey($payment->user_id)->lockForUpdate()->firstOrFail();
        $user->balance = round((float) $user->balance + (float) $payment->amount, 2);
        $user->save();
    }

    protected function doneUrl(OnlinePayment $payment): string
    {
        return route('user_balance');
    }

    protected function paidMessage(): string
    {
        return 'تم اضافة الرصيد الخاص بك بنجاح';
    }
}
