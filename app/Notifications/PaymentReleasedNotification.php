<?php

namespace App\Notifications;

use App\Models\RequestPayment;

/** To the freelancer: the client confirmed the work and the money is in their balance. */
class PaymentReleasedNotification extends ServiceRequestNotification
{
    protected ?string $emailKey = 'payment_released';

    /** $total = everything released for the request (first payment + paid differences). */
    public function __construct(protected RequestPayment $payment, protected ?float $total = null)
    {
    }

    protected function text(): string
    {
        return \App\Support\Msg::t('payments.request_released', [
            'number' => $this->payment->request->number,
            'amount' => \App\Support\Money::format($this->total ?? $this->payment->freelancer_amount),
        ]);
    }

    protected function path(): string
    {
        return '/balance';
    }
}
