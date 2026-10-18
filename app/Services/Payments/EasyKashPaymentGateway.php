<?php

namespace App\Services\Payments;

use App\Models\OnlinePayment;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

/**
 * EasyKash, as integrated in the AyaKids project: direct pay link, inquiry by
 * customerReference, and an HMAC-SHA512 signed webhook. Our customerReference
 * is the OnlinePayment id. Cash methods (Fawry, wallets) can be paid hours
 * later, so a payment may stay pending; `payments:sync` settles it.
 */
class EasyKashPaymentGateway implements PaymentGateway
{
    public const DEAD_STATUSES = ['FAILED', 'EXPIRED', 'CANCELED', 'CANCELLED'];

    private function config(string $key, $default = null)
    {
        return config('services.easykash.' . $key, $default);
    }

    private function http()
    {
        $key = $this->config('api_key');
        if (! $key) {
            throw new RuntimeException('الدفع غير مُفعّل: مفتاح EasyKash غير مضبوط');
        }

        return Http::withHeaders(['authorization' => $key, 'Content-Type' => 'application/json'])->timeout(15);
    }

    public function createCheckout(OnlinePayment $payment, string $returnUrl): CheckoutSession
    {
        $payload = [
            'amount' => (float) $payment->amount,
            'currency' => strtoupper($payment->currency),
            'cashExpiry' => (int) $this->config('cash_expiry_hours', 24),
            'name' => $payment->contact_name,
            'email' => $payment->contact_email,
            'mobile' => self::localMobile((string) $payment->contact_mobile),
            'redirectUrl' => $returnUrl,
            'customerReference' => (int) $payment->id,
        ];
        $options = array_filter(array_map('intval', explode(',', (string) $this->config('payment_options', ''))));
        if ($options) {
            $payload['paymentOptions'] = array_values($options);
        }

        $response = $this->http()->post($this->config('base_url') . '/directpayv1/pay', $payload);
        $url = $response->successful() ? $response->json('redirectUrl') : null;
        if (! $url) {
            Log::error('EasyKash pay failed', ['payment' => $payment->id, 'status' => $response->status(), 'body' => $response->body()]);
            throw new RuntimeException('تعذّر بدء الدفع عبر EasyKash الآن، حاول مرة أخرى بعد قليل');
        }

        return new CheckoutSession(reference: (string) $payment->id, status: CheckoutSession::PENDING, url: $url);
    }

    public function retrieveCheckout(OnlinePayment $payment): CheckoutSession
    {
        $response = $this->http()->post($this->config('base_url') . '/cash-api/inquire', [
            'customerReference' => (string) $payment->id,
        ]);
        if (! $response->successful()) {
            Log::warning('EasyKash inquiry failed', ['payment' => $payment->id, 'status' => $response->status(), 'body' => $response->body()]);
            throw new RuntimeException('تعذّر التحقق من الدفع لدى EasyKash الآن');
        }

        return $this->toSession((string) $payment->id, (array) $response->json());
    }

    public function parseWebhook(array $payload): ?CheckoutSession
    {
        // The EasyKash dashboard may not offer an HMAC secret (AyaKids has none).
        // Without it the signature cannot be checked, which is fine because the
        // caller never trusts the payload: it asks EasyKash about the payment.
        if ($secret = $this->config('hmac_secret')) {
            $signed = implode('', array_map(fn ($key) => (string) ($payload[$key] ?? ''), [
                'ProductCode', 'Amount', 'ProductType', 'PaymentMethod', 'status', 'easykashRef', 'customerReference',
            ]));
            if (! hash_equals(hash_hmac('sha512', $signed, $secret), (string) ($payload['signatureHash'] ?? ''))) {
                throw new RuntimeException('Invalid EasyKash signature');
            }
        }

        $reference = (string) ($payload['customerReference'] ?? '');

        return $reference === '' ? null : $this->toSession($reference, $payload);
    }

    public function refund(OnlinePayment $payment): ?string
    {
        // EasyKash refunds are done from the EasyKash dashboard.
        return null;
    }

    /** "+20 100 000 0001" / "00201000000001" → "01000000001" (other numbers: digits only). */
    public static function localMobile(string $phone): string
    {
        $digits = preg_replace('/\D/', '', $phone);
        if (str_starts_with($digits, '00')) {
            $digits = substr($digits, 2);
        }
        if (str_starts_with($digits, '20') && strlen($digits) === 12) {
            $digits = '0' . substr($digits, 2);
        }

        return $digits;
    }

    private function toSession(string $reference, array $data): CheckoutSession
    {
        $status = strtoupper((string) ($data['status'] ?? ''));

        return new CheckoutSession(
            reference: $reference,
            status: match (true) {
                $status === 'PAID' => CheckoutSession::PAID,
                in_array($status, self::DEAD_STATUSES, true) => CheckoutSession::FAILED,
                default => CheckoutSession::PENDING,
            },
            amount: isset($data['Amount']) && is_numeric($data['Amount']) ? (float) $data['Amount'] : (isset($data['amount']) && is_numeric($data['amount']) ? (float) $data['amount'] : null),
            transaction: isset($data['easykashRef']) ? (string) $data['easykashRef'] : null,
        );
    }
}
