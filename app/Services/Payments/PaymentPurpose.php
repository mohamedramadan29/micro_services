<?php

namespace App\Services\Payments;

use App\Models\OnlinePayment;
use Illuminate\Http\RedirectResponse;

/** What a paid (or failed) OnlinePayment does on the site. */
interface PaymentPurpose
{
    /**
     * Runs exactly once per payment, inside the transaction that marks it
     * paid, and only after EasyKash itself confirmed it.
     */
    public function paid(OnlinePayment $payment): void;

    /** The payment failed or expired at EasyKash. */
    public function failed(OnlinePayment $payment): void;

    /** Where the payer lands when coming back from EasyKash. */
    public function response(OnlinePayment $payment): RedirectResponse;
}
