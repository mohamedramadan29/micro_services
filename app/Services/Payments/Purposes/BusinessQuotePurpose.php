<?php

namespace App\Services\Payments\Purposes;

use App\Models\BusinessRequest;
use App\Models\OnlinePayment;
use App\Services\Business\BusinessService;

/** «نفّذها للأعمال»: a company pays the accepted quote of its request online (the admin may also record a payment). */
class BusinessQuotePurpose extends SitePurpose
{
    public function paid(OnlinePayment $payment): void
    {
        $request = BusinessRequest::lockForUpdate()->find($payment->data('business_request_id'));
        if ($request) {
            app(BusinessService::class)->markPaid($request, 'online', 'EasyKash #' . $payment->id, null);
        }
    }

    protected function doneUrl(OnlinePayment $payment): string
    {
        $request = BusinessRequest::find($payment->data('business_request_id'));

        return $request ? route('business.requests.show', $request) : route('business.mine');
    }

    protected function paidMessage(): string
    {
        return 'تم الدفع بنجاح، وسيتواصل معك فريق نفّذها للأعمال لبدء التنفيذ.';
    }
}
