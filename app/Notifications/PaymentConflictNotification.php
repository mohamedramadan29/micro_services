<?php

namespace App\Notifications;

use App\Models\RequestPayment;

/** To admins: a client paid for a request that was already taken; refund needed. */
class PaymentConflictNotification extends ServiceRequestNotification
{
    protected ?string $emailKey = 'admin_alerts';

    public function __construct(protected RequestPayment $payment)
    {
    }

    protected function text(): string
    {
        $key = $this->payment->service_request_change_id ? 'admin.request_payment_change_cancelled' : 'admin.request_payment_duplicate';

        return \App\Support\Msg::t($key, [
            'number' => $this->payment->request->number, 'amount' => \App\Support\Money::format($this->payment->amount),
        ]);
    }

    protected function path(): string
    {
        return '/admin/requests/' . $this->payment->request->number;
    }
}
