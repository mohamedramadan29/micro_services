<?php

namespace App\Console\Commands;

use App\Models\OnlinePayment;
use App\Services\Payments\OnlinePaymentService;
use Illuminate\Console\Command;

/**
 * Settle unpaid payments by asking EasyKash, like AyaKids'
 * easykash:sync-pending: Fawry vouchers and wallet payments are often paid
 * after the payer left, without coming back through the return page.
 */
class SyncPendingPayments extends Command
{
    protected $signature = 'payments:sync {--days=3 : Only payments started within this many days}';

    protected $description = 'Ask EasyKash about pending payments (service requests, orders, courses, packages, balance)';

    public function handle(OnlinePaymentService $payments): int
    {
        $counts = [];
        OnlinePayment::whereIn('status', [OnlinePayment::STATUS_PENDING, OnlinePayment::STATUS_CANCELLED])
            ->where('created_at', '>=', now()->subDays((int) $this->option('days')))
            ->lazyById(200)
            ->each(function (OnlinePayment $payment) use ($payments, &$counts) {
                try {
                    $status = $payments->sync($payment)->status;
                } catch (\Throwable $e) {
                    $status = 'unreachable';
                }
                $counts[$status] = ($counts[$status] ?? 0) + 1;
            });

        $this->info('Checked pending payments: ' . json_encode($counts));

        return self::SUCCESS;
    }
}
