<?php

namespace App\Services\Payments;

/** What the site needs to know about a payment at the gateway. */
class CheckoutSession
{
    public const PAID = 'paid';
    public const PENDING = 'pending'; // e.g. a Fawry voucher or wallet payment not completed yet
    public const FAILED = 'failed';   // failed, expired or cancelled at the gateway

    public function __construct(
        public string $reference,          // our reference at the gateway (the payment id)
        public string $status = self::PENDING,
        public ?string $url = null,        // where to send the client (on creation)
        public ?float $amount = null,      // when the gateway reports it
        public ?string $transaction = null, // the gateway's own id
    ) {
    }

    public function isPaid(): bool
    {
        return $this->status === self::PAID;
    }

    public function isFailed(): bool
    {
        return $this->status === self::FAILED;
    }
}
