<?php

namespace Tests\Fakes;

use App\Models\OnlinePayment;
use App\Services\Payments\CheckoutSession;
use App\Services\Payments\PaymentGateway;
use RuntimeException;

/** In-memory gateway: tests decide what happened to each payment. */
class FakePaymentGateway implements PaymentGateway
{
    /** @var array<string, CheckoutSession> keyed by our reference (OnlinePayment id) */
    public array $sessions = [];

    /** @var array<int, string> payment id => refund reference */
    public array $refunds = [];

    public bool $manualRefunds = false;

    public bool $failRefunds = false;

    public bool $unreachable = false;

    public function createCheckout(OnlinePayment $payment, string $returnUrl): CheckoutSession
    {
        return $this->sessions[(string) $payment->id] = new CheckoutSession(
            reference: (string) $payment->id,
            url: 'https://pay.test/' . $payment->id,
            amount: (float) $payment->amount,
        );
    }

    public function markPaid(string $reference, ?float $amount = null): CheckoutSession
    {
        $session = $this->sessions[$reference];
        $session->status = CheckoutSession::PAID;
        $session->transaction = 'ek_' . $reference;
        if ($amount !== null) {
            $session->amount = $amount;
        }

        return $session;
    }

    public function markFailed(string $reference): CheckoutSession
    {
        $session = $this->sessions[$reference];
        $session->status = CheckoutSession::FAILED;

        return $session;
    }

    public function retrieveCheckout(OnlinePayment $payment): CheckoutSession
    {
        if ($this->unreachable) {
            throw new RuntimeException('gateway unreachable');
        }

        return $this->sessions[(string) $payment->id] ?? throw new RuntimeException('unknown payment');
    }

    public function parseWebhook(array $payload): ?CheckoutSession
    {
        if (($payload['signatureHash'] ?? null) !== 'valid-signature') {
            throw new RuntimeException('bad signature');
        }

        return $this->sessions[(string) $payload['customerReference']] ?? null;
    }

    public function refund(OnlinePayment $payment): ?string
    {
        if ($this->failRefunds) {
            throw new RuntimeException('refund failed');
        }

        return $this->manualRefunds ? null : ($this->refunds[$payment->id] = 're_' . $payment->id);
    }
}
