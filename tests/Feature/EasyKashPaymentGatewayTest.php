<?php

namespace Tests\Feature;

use App\Models\OnlinePayment;
use App\Services\Payments\CheckoutSession;
use App\Services\Payments\EasyKashPaymentGateway;
use Illuminate\Http\Client\Request as HttpRequest;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Tests\TestCase;

class EasyKashPaymentGatewayTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['services.easykash' => [
            'api_key' => 'ek_test_key', 'hmac_secret' => 'ek_hmac', 'base_url' => 'https://easykash.test/api',
            'currency' => 'EGP', 'payment_options' => '2,4,5,6', 'cash_expiry_hours' => 24,
        ]]);
    }

    private function payment(): OnlinePayment
    {
        $payment = new OnlinePayment([
            'amount' => 450, 'currency' => 'EGP',
            'contact_name' => 'سارة', 'contact_email' => 'sara@example.com', 'contact_mobile' => '+20 100 000-0001',
        ]);
        $payment->id = 7;

        return $payment;
    }

    private function signed(array $payload, string $secret = 'ek_hmac'): array
    {
        $data = implode('', array_map(fn ($k) => (string) ($payload[$k] ?? ''), ['ProductCode', 'Amount', 'ProductType', 'PaymentMethod', 'status', 'easykashRef', 'customerReference']));

        return $payload + ['signatureHash' => hash_hmac('sha512', $data, $secret)];
    }

    public function test_create_sends_the_ayakids_payload_and_returns_the_redirect(): void
    {
        Http::fake(['easykash.test/api/directpayv1/pay' => Http::response(['redirectUrl' => 'https://pay.easykash.net/abc'])]);

        $session = (new EasyKashPaymentGateway())->createCheckout($this->payment(), 'https://site.test/payments/return/7/token');

        $this->assertSame('https://pay.easykash.net/abc', $session->url);
        $this->assertSame('7', $session->reference);
        Http::assertSent(fn (HttpRequest $request) => $request->hasHeader('authorization', 'ek_test_key')
            && $request['amount'] == 450 && $request['currency'] === 'EGP' && $request['customerReference'] === 7
            && $request['mobile'] === '01000000001' && $request['paymentOptions'] === [2, 4, 5, 6]
            && $request['redirectUrl'] === 'https://site.test/payments/return/7/token');
    }

    public function test_create_fails_loudly_without_a_redirect_or_a_key(): void
    {
        Http::fake(['*' => Http::response(['message' => 'error'], 422)]);
        try {
            (new EasyKashPaymentGateway())->createCheckout($this->payment(), 'https://x');
            $this->fail('expected an exception');
        } catch (RuntimeException) {
        }

        config(['services.easykash.api_key' => null]);
        $this->expectException(RuntimeException::class);
        (new EasyKashPaymentGateway())->createCheckout($this->payment(), 'https://x');
    }

    public function test_inquiry_maps_the_status(): void
    {
        Http::fakeSequence('easykash.test/api/cash-api/inquire')
            ->push(['status' => 'PAID', 'easykashRef' => 'EK1', 'Amount' => '450'])
            ->push(['status' => 'PENDING'])
            ->push(['status' => 'EXPIRED']);
        $gateway = new EasyKashPaymentGateway();

        $paid = $gateway->retrieveCheckout($this->payment());
        $this->assertTrue($paid->isPaid());
        $this->assertSame('EK1', $paid->transaction);
        $this->assertSame(450.0, $paid->amount);
        $this->assertSame(CheckoutSession::PENDING, $gateway->retrieveCheckout($this->payment())->status);
        $this->assertTrue($gateway->retrieveCheckout($this->payment())->isFailed());
        Http::assertSent(fn (HttpRequest $request) => $request['customerReference'] === '7');
    }

    public function test_webhook_signature_is_verified(): void
    {
        $gateway = new EasyKashPaymentGateway();
        $payload = ['ProductCode' => 'P1', 'Amount' => '450', 'ProductType' => 'Direct', 'PaymentMethod' => 'Fawry', 'status' => 'PAID', 'easykashRef' => 'EK9', 'customerReference' => '7'];

        $session = $gateway->parseWebhook($this->signed($payload));
        $this->assertSame('7', $session->reference);
        $this->assertTrue($session->isPaid());

        $this->expectException(RuntimeException::class);
        $gateway->parseWebhook($this->signed($payload, 'someone-else'));
    }

    public function test_without_a_secret_the_webhook_is_decoded_unsigned(): void
    {
        config(['services.easykash.hmac_secret' => null]);

        $session = (new EasyKashPaymentGateway())->parseWebhook(['status' => 'PAID', 'customerReference' => '7']);
        $this->assertSame('7', $session->reference);
    }

    public function test_refunds_are_manual(): void
    {
        $this->assertNull((new EasyKashPaymentGateway())->refund($this->payment()));
    }

    public function test_mobile_numbers_are_sent_in_local_format(): void
    {
        foreach (['+20 100 000 0001', '00201000000001', '0100-000-0001'] as $phone) {
            $this->assertSame('01000000001', EasyKashPaymentGateway::localMobile($phone));
        }
    }
}
