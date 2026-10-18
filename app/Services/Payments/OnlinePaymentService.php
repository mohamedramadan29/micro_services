<?php

namespace App\Services\Payments;

use App\Models\OnlinePayment;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Every payment on the site: start it at EasyKash, then apply what EasyKash
 * reports (return page, webhook, `payments:sync`) through the payment's
 * purpose. Applying is idempotent and only trusts EasyKash, never the browser.
 */
class OnlinePaymentService
{
    /** @var array<string, class-string<PaymentPurpose>> */
    public const PURPOSES = [
        OnlinePayment::PURPOSE_REQUEST => RequestPaymentService::class,
        OnlinePayment::PURPOSE_SERVICE_ORDER => Purposes\ServiceOrderPurpose::class,
        OnlinePayment::PURPOSE_PRODUCT_ORDER => Purposes\ProductOrderPurpose::class,
        OnlinePayment::PURPOSE_PACKAGE => Purposes\PackagePurpose::class,
        OnlinePayment::PURPOSE_COURSE => Purposes\CoursePurpose::class,
        OnlinePayment::PURPOSE_BALANCE => Purposes\BalancePurpose::class,
        OnlinePayment::PURPOSE_CONTRACT_STAGE => Purposes\ContractStagePurpose::class,
    ];

    public function __construct(private PaymentGateway $gateway)
    {
    }

    /**
     * Record a pending payment. Contact details default to the user's.
     *
     * @param  array{name?: ?string, email?: ?string, mobile?: ?string}  $contact
     */
    public function create(string $purpose, ?User $user, float $amount, string $description, array $payload = [], array $contact = []): OnlinePayment
    {
        if (! isset(self::PURPOSES[$purpose])) {
            throw new RuntimeException('Unknown payment purpose: ' . $purpose);
        }
        if ($amount <= 0) {
            throw new RuntimeException('قيمة الدفع غير صحيحة');
        }

        // Journey analytics: the visit that started the payment (none without the visitor's consent).
        $visit = app(\App\Services\Analytics\Tracker::class)->visit();

        $payment = OnlinePayment::create([
            'visit_id' => $visit?->id,
            'user_id' => $user?->id,
            'purpose' => $purpose,
            'description' => Str::limit($description, 250),
            'amount' => round($amount, 2),
            'currency' => strtoupper(config('services.easykash.currency', 'EGP')),
            'status' => OnlinePayment::STATUS_PENDING,
            'payload' => $payload,
            'contact_name' => $contact['name'] ?? $user?->name,
            'contact_email' => $contact['email'] ?? $user?->email,
            'contact_mobile' => $contact['mobile'] ?? $user?->phone,
            'return_token' => Str::random(40),
        ]);
        if ($visit) {
            \App\Services\Analytics\Tracker::recordOn($visit, 'payment_start', ['meta' => ['purpose' => $purpose, 'amount' => round($amount, 2)]]);
        }

        return $payment;
    }

    /** Open the EasyKash page for a pending payment and return its URL. */
    public function checkout(OnlinePayment $payment): string
    {
        try {
            return $this->gateway->createCheckout($payment, $this->returnUrl($payment))->url;
        } catch (\Throwable $e) {
            $payment->update(['status' => OnlinePayment::STATUS_CANCELLED]);
            $this->purpose($payment)->failed($payment);
            throw $e;
        }
    }

    /** create() + checkout(). */
    public function start(string $purpose, ?User $user, float $amount, string $description, array $payload = [], array $contact = []): string
    {
        return $this->checkout($this->create($purpose, $user, $amount, $description, $payload, $contact));
    }

    public function returnUrl(OnlinePayment $payment): string
    {
        return route('payments.return', ['onlinePayment' => $payment->id, 'token' => $payment->return_token]);
    }

    /** Ask EasyKash about a payment that is not settled yet and apply the answer. */
    public function sync(OnlinePayment $payment): OnlinePayment
    {
        if (! in_array($payment->status, [OnlinePayment::STATUS_PENDING, OnlinePayment::STATUS_CANCELLED], true)) {
            return $payment;
        }

        return $this->apply($this->gateway->retrieveCheckout($payment)) ?? $payment;
    }

    /**
     * Apply what EasyKash reported. Safe to call many times and in any order:
     * only the first paid call acts, and a late failure never undoes a payment.
     */
    public function apply(CheckoutSession $session): ?OnlinePayment
    {
        $payment = ctype_digit($session->reference) ? OnlinePayment::find((int) $session->reference) : null;
        if (! $payment) {
            return null;
        }
        if ($session->isFailed()) {
            if ($payment->status === OnlinePayment::STATUS_PENDING) {
                $payment->update(['status' => OnlinePayment::STATUS_FAILED]);
                $this->purpose($payment)->failed($payment);
                $this->journey($payment, 'payment_failed');
            }

            return $payment;
        }
        if (! $session->isPaid()) {
            return $payment;
        }
        // Cash methods may report the amount including the gateway fee, so only
        // a payment below the price is refused.
        if ($session->amount !== null && $session->amount + 0.009 < (float) $payment->amount) {
            Log::error('Online payment amount below the price', ['payment' => $payment->id, 'reported' => $session->amount]);
            throw new RuntimeException('المبلغ المدفوع أقل من المطلوب');
        }

        return DB::transaction(function () use ($payment, $session) {
            $payment = OnlinePayment::whereKey($payment->id)->lockForUpdate()->first();
            if ($payment->isPaid()) {
                return $payment;
            }

            $payment->update([
                'status' => OnlinePayment::STATUS_PAID,
                'paid_at' => now(),
                'paid_amount' => $session->amount ?? $payment->amount,
                'gateway_transaction' => $session->transaction,
            ]);
            $this->purpose($payment)->paid($payment);
            $this->journey($payment, 'order_complete');

            return $payment;
        });
    }

    /** Funnel step on the visit that started the payment (confirmed later, often without a browser). */
    private function journey(OnlinePayment $payment, string $type): void
    {
        $visit = $payment->visit_id ? \App\Models\AnalyticsVisit::find($payment->visit_id) : null;
        if ($visit) {
            \App\Services\Analytics\Tracker::recordOn($visit, $type, ['meta' => ['purpose' => $payment->purpose, 'amount' => (float) $payment->amount]]);
        }
    }

    public function purpose(OnlinePayment $payment): PaymentPurpose
    {
        return app(self::PURPOSES[$payment->purpose] ?? throw new RuntimeException('Unknown payment purpose: ' . $payment->purpose));
    }
}
