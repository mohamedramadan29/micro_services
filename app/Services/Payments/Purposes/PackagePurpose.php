<?php

namespace App\Services\Payments\Purposes;

use App\Models\admin\PackageSubscribe;
use App\Models\OnlinePayment;

/** Subscribing to a plan on the packages page. */
class PackagePurpose extends SitePurpose
{
    public function paid(OnlinePayment $payment): void
    {
        $subscribe = new PackageSubscribe();
        $subscribe->user_id = $payment->user_id;
        $subscribe->package_id = $payment->data('package_id');
        $subscribe->package_name = $payment->data('package_name');
        $subscribe->user_name = $payment->user?->name ?? (string) $payment->contact_name;
        $subscribe->price = (float) $payment->amount;
        $subscribe->save();
    }

    protected function doneUrl(OnlinePayment $payment): string
    {
        return route('packages.index');
    }

    protected function paidMessage(): string
    {
        return 'تم الاشتراك في الخطة بنجاح سيتم التواصل معك في اقرب وقت ممكن';
    }
}
