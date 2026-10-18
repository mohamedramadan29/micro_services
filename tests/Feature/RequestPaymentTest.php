<?php

namespace Tests\Feature;

use App\Models\admin\Category;
use App\Models\admin\Setting;
use App\Models\admin\SubCategory;
use App\Models\OnlinePayment;
use App\Models\RequestOffer;
use App\Models\RequestPayment;
use App\Models\ServiceRequest;
use App\Models\User;
use App\Models\WalletTransaction;
use App\Notifications\OfferNotSelectedNotification;
use App\Notifications\OfferSelectedNotification;
use App\Notifications\PaymentReleasedNotification;
use App\Notifications\WorkDeliveredNotification;
use App\Services\Payments\PaymentGateway;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\Fakes\FakePaymentGateway;
use Tests\TestCase;

class RequestPaymentTest extends TestCase
{
    use RefreshDatabase;

    private FakePaymentGateway $gateway;
    private User $client;
    private User $freelancer;
    private User $other;
    private ServiceRequest $request;
    private RequestOffer $offer;
    private RequestOffer $otherOffer;

    protected function setUp(): void
    {
        parent::setUp();
        Notification::fake();
        $this->gateway = new FakePaymentGateway();
        $this->app->instance(PaymentGateway::class, $this->gateway);
        Setting::create(['website_commission' => '10', 'website_balance' => 100]);

        $category = Category::create(['name' => 'تسويق', 'slug' => 'm', 'image' => 'x.png', 'status' => 1]);
        $service = SubCategory::create(['name' => 'إعلانات المواقع', 'slug' => 'ads', 'image' => 'x.png', 'parent_id' => $category->id, 'status' => 1]);

        $this->client = User::factory()->create();
        $this->freelancer = User::factory()->freelancer()->create(['balance' => 20]);
        $this->other = User::factory()->freelancer()->create();
        foreach ([$this->freelancer, $this->other] as $freelancer) {
            $freelancer->specializations()->attach($service->id);
        }

        $this->request = ServiceRequest::create([
            'user_id' => $this->client->id, 'category_id' => $category->id, 'sub_category_id' => $service->id,
            'service_name' => 'إعلانات المواقع', 'path' => 'execute', 'status' => ServiceRequest::STATUS_OPEN,
            'problem' => 'حملة', 'contact_name' => 'x', 'contact_phone' => '01000000000', 'contact_email' => 'c@example.com', 'published_at' => now(),
        ]);
        $this->request->update(['number' => 'REQ-' . (1000 + $this->request->id)]);

        $make = fn (User $freelancer, $price) => $this->request->offers()->create([
            'freelancer_id' => $freelancer->id, 'price' => $price, 'duration_days' => 7, 'scope' => str_repeat('نطاق ', 5), 'message' => str_repeat('رسالة ', 5),
        ]);
        $this->offer = $make($this->freelancer, 450);
        $this->otherOffer = $make($this->other, 380);
    }

    /** Client accepts an offer; returns our gateway reference (the OnlinePayment id). */
    private function accept(?RequestOffer $offer = null): string
    {
        $this->actingAs($this->client)->post(route('offers.accept', $offer ?? $this->offer))->assertRedirect();

        return array_key_last($this->gateway->sessions);
    }

    private function back(string $reference)
    {
        $token = OnlinePayment::findOrFail($reference)->return_token;

        return $this->actingAs($this->client)->get(route('payments.return', ['onlinePayment' => $reference, 'token' => $token, 'status' => 'PAID']));
    }

    private function payment(string $reference): RequestPayment
    {
        return RequestPayment::where('online_payment_id', $reference)->firstOrFail();
    }

    private function payAndReturn(string $reference)
    {
        $this->gateway->markPaid($reference);

        return $this->back($reference);
    }

    public function test_choosing_an_offer_starts_an_easykash_payment_in_egp(): void
    {
        $this->actingAs($this->client)->post(route('offers.accept', $this->offer))->assertRedirect('https://pay.test/1');

        $payment = RequestPayment::firstOrFail();
        $this->assertSame(RequestPayment::STATUS_PENDING, $payment->status);
        $this->assertSame('EGP', $payment->currency);
        $this->assertSame('easykash', $payment->gateway);
        $this->assertSame((string) $payment->online_payment_id, $payment->gateway_reference);
        $online = $payment->onlinePayment;
        $this->assertSame(OnlinePayment::PURPOSE_REQUEST, $online->purpose);
        $this->assertEquals(450, $online->amount);
        $this->assertSame(['x', 'c@example.com', '01000000000'], [$online->contact_name, $online->contact_email, $online->contact_mobile]);
        $this->assertSame(ServiceRequest::STATUS_OPEN, $this->request->fresh()->status);

        $this->actingAs($this->freelancer)->post(route('offers.accept', $this->offer))->assertForbidden();
        $this->actingAs(User::factory()->create())->post(route('offers.accept', $this->offer))->assertForbidden();
    }

    public function test_the_return_page_never_trusts_the_query_string(): void
    {
        $reference = $this->accept();

        // ?status=PAID in the URL, but EasyKash says it is still pending (e.g. a Fawry voucher).
        $this->back($reference)->assertSessionHas('Success_message', fn ($m) => str_contains($m, 'بانتظار إتمام الدفع'));
        $this->assertSame(RequestPayment::STATUS_PENDING, RequestPayment::first()->status);
        $this->assertSame(ServiceRequest::STATUS_OPEN, $this->request->fresh()->status);
    }

    public function test_paid_payment_holds_the_money_and_closes_other_offers(): void
    {
        $reference = $this->accept();
        $this->payAndReturn($reference)->assertRedirect(route('requests.show', $this->request))->assertSessionHas('Success_message');

        $payment = RequestPayment::firstOrFail();
        $this->assertSame(RequestPayment::STATUS_PAID, $payment->status);
        $this->assertEquals(10, $payment->commission_rate);
        $this->assertEquals(45, $payment->commission_amount);
        $this->assertEquals(405, $payment->freelancer_amount);
        $this->assertSame('ek_' . $reference, $payment->gateway_transaction);

        $this->assertSame(RequestOffer::STATUS_ACCEPTED, $this->offer->fresh()->status);
        $this->assertSame(RequestOffer::STATUS_REJECTED, $this->otherOffer->fresh()->status);
        $this->assertSame(ServiceRequest::STATUS_IN_PROGRESS, $this->request->fresh()->status);
        $this->assertEquals(20, $this->freelancer->fresh()->balance);

        Notification::assertSentTo($this->freelancer, OfferSelectedNotification::class);
        Notification::assertSentTo($this->other, OfferNotSelectedNotification::class);

        // Returning again, a webhook or the sync never apply it twice.
        $this->back($reference);
        $this->call('POST', route('easykash.webhook'), ['customerReference' => $reference, 'status' => 'PAID', 'signatureHash' => 'valid-signature'])->assertOk();
        $this->artisan('payments:sync');
        $this->assertSame(1, WalletTransaction::where('type', WalletTransaction::TYPE_ESCROW_IN)->count());
        Notification::assertSentToTimes($this->freelancer, OfferSelectedNotification::class, 1);

        $this->actingAs($this->other)->get(route('freelancer.requests.index'))->assertDontSee($this->request->number);
    }

    public function test_a_failed_payment_frees_the_request_again(): void
    {
        $reference = $this->accept();
        $this->gateway->markFailed($reference);

        $this->back($reference)->assertSessionHasErrors();
        $this->assertSame(RequestPayment::STATUS_CANCELLED, RequestPayment::first()->status);
        $this->assertSame(ServiceRequest::STATUS_OPEN, $this->request->fresh()->status);

        // A late "failed" notice never undoes a payment that went through.
        $paid = $this->accept();
        $this->payAndReturn($paid);
        $this->gateway->markFailed($paid);
        $this->artisan('payments:sync');
        $this->call('POST', route('easykash.webhook'), ['customerReference' => $paid, 'status' => 'FAILED', 'signatureHash' => 'valid-signature']);
        $this->assertSame(RequestPayment::STATUS_PAID, $this->payment($paid)->status);
    }

    public function test_the_return_link_needs_its_secret_token(): void
    {
        $reference = $this->accept();
        $this->gateway->markPaid($reference);

        $this->actingAs(User::factory()->create())->get(route('payments.return', ['onlinePayment' => $reference, 'token' => 'guessed']))->assertNotFound();
        $this->assertSame(RequestPayment::STATUS_PENDING, RequestPayment::first()->status);
    }

    public function test_amount_mismatch_is_not_accepted(): void
    {
        $reference = $this->accept();
        $this->gateway->markPaid($reference, 100);

        $this->back($reference);
        $this->assertSame(RequestPayment::STATUS_PENDING, RequestPayment::first()->status);
        $this->assertSame(ServiceRequest::STATUS_OPEN, $this->request->fresh()->status);
    }

    public function test_paying_an_older_payment_after_another_offer_won_is_flagged_for_refund(): void
    {
        $first = $this->accept($this->otherOffer);
        $second = $this->accept($this->offer);
        $this->assertSame(RequestPayment::STATUS_CANCELLED, $this->payment($first)->status);

        $this->payAndReturn($second);
        // The older Fawry voucher gets paid later and only the webhook reports it.
        $this->gateway->markPaid($first);
        $this->call('POST', route('easykash.webhook'), ['customerReference' => $first, 'status' => 'PAID', 'signatureHash' => 'valid-signature'])->assertOk();

        $this->assertSame(RequestPayment::STATUS_CONFLICT, $this->payment($first)->status);
        $this->assertSame($this->offer->id, $this->request->fresh()->accepted_offer_id);
    }

    public function test_webhook_and_sync_settle_payments_the_client_never_came_back_from(): void
    {
        $reference = $this->accept();
        $this->gateway->markPaid($reference);

        $this->call('POST', route('easykash.webhook'), ['customerReference' => $reference, 'status' => 'PAID', 'signatureHash' => 'forged'])->assertStatus(401);
        $this->assertSame(RequestPayment::STATUS_PENDING, RequestPayment::first()->status);

        $this->artisan('payments:sync')->expectsOutputToContain('"paid":1');
        $this->assertSame(RequestPayment::STATUS_PAID, RequestPayment::first()->status);
    }

    public function test_the_return_page_copes_with_easykash_being_unreachable(): void
    {
        $reference = $this->accept();
        $this->gateway->unreachable = true;

        $this->back($reference)->assertRedirect(route('requests.show', $this->request))->assertSessionHas('Success_message');
        $this->assertSame(RequestPayment::STATUS_PENDING, RequestPayment::first()->status);
    }

    public function test_delivery_then_confirmation_pays_the_freelancer_minus_commission(): void
    {
        $this->payAndReturn($this->accept());

        $this->actingAs($this->other)->post(route('freelancer.requests.deliver', $this->request))->assertForbidden();
        $this->actingAs($this->freelancer)->post(route('freelancer.requests.deliver', $this->request), ['delivery_note' => 'سلّمت الحملة'])
            ->assertSessionHasNoErrors();
        $this->assertNotNull($this->request->fresh()->delivered_at);
        Notification::assertSentTo($this->client, WorkDeliveredNotification::class);

        Setting::first()->update(['website_commission' => '30']);

        $this->actingAs($this->freelancer)->post(route('requests.complete', $this->request))->assertForbidden();
        $this->actingAs($this->client)->post(route('requests.complete', $this->request))->assertSessionHas('Success_message');

        $this->assertEquals(425, $this->freelancer->fresh()->balance);
        $this->assertEquals(145, Setting::first()->website_balance);
        $this->assertSame(RequestPayment::STATUS_RELEASED, RequestPayment::first()->status);
        $this->assertSame(ServiceRequest::STATUS_COMPLETED, $this->request->fresh()->status);
        Notification::assertSentTo($this->freelancer, PaymentReleasedNotification::class);

        $this->actingAs($this->client)->post(route('requests.complete', $this->request))->assertSessionHasErrors();
        $this->assertEquals(425, $this->freelancer->fresh()->balance);
    }

    public function test_pages_show_the_progress_and_amounts_in_egp(): void
    {
        $this->payAndReturn($this->accept());

        $this->actingAs($this->client)->get(route('requests.show', $this->request))
            ->assertOk()->assertSee('المبلغ محجوز لدى نفّذها')->assertSee('450 ج.م')->assertDontSee('اختيار العرض والدفع');
        $this->actingAs($this->freelancer)->get(route('freelancer.requests.show', $this->request))
            ->assertOk()->assertSee('تم اختيار عرضك')->assertSee('405 ج.م')->assertSee('تسليم العمل');
        $this->actingAs($this->other)->get(route('freelancer.requests.show', $this->request))
            ->assertOk()->assertSee('لم يُختر')->assertDontSee('تسليم العمل');
    }

    public function test_offers_cannot_be_chosen_once_the_request_is_taken(): void
    {
        $this->payAndReturn($this->accept());

        $this->actingAs($this->client)->post(route('offers.accept', $this->otherOffer))->assertSessionHasErrors();
        $this->assertSame(1, RequestPayment::count());
    }

    public function test_gateway_fees_on_top_of_the_price_are_accepted(): void
    {
        $reference = $this->accept();
        $this->gateway->markPaid($reference, 455.5);

        $this->back($reference);
        $this->assertSame(RequestPayment::STATUS_PAID, RequestPayment::first()->status);
    }

    public function test_freelancer_names_cannot_inject_script_into_the_accept_button(): void
    {
        $this->freelancer->update(['name' => "x');alert(document.cookie);('\"<b>"]);

        $html = $this->actingAs($this->client)->get(route('requests.show', $this->request))->assertOk()->getContent();

        $this->assertStringNotContainsString("x');alert", $html);
        $this->assertStringContainsString('x\u0027);alert(document.cookie);(\u0027\u0022\u003Cb\u003E', $html);
    }
}
