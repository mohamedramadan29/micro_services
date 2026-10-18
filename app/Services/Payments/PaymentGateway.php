<?php

namespace App\Services\Payments;

use App\Models\OnlinePayment;

/** Hosted payment for every OnlinePayment (EasyKash in production, a fake in tests). */
interface PaymentGateway
{
    /** Start a hosted payment; the client is sent to the returned session's url. */
    public function createCheckout(OnlinePayment $payment, string $returnUrl): CheckoutSession;

    /** Ask the gateway what happened to this payment (never trust the browser). */
    public function retrieveCheckout(OnlinePayment $payment): CheckoutSession;

    /**
     * Decode a server-to-server notification (checking its signature when the
     * gateway signs it); null when it is not about a payment. Its status is
     * only a hint: the payment is always confirmed with retrieveCheckout().
     */
    public function parseWebhook(array $payload): ?CheckoutSession;

    /**
     * Refund a payment in full and return a reference. Gateways without a
     * refund API return null: the admin refunds from the gateway dashboard
     * and confirms it on the site.
     */
    public function refund(OnlinePayment $payment): ?string;
}
