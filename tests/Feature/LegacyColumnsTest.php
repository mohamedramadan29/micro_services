<?php

namespace Tests\Feature;

use App\Models\admin\Product;
use App\Models\admin\Setting;
use App\Models\front\Conversation;
use App\Models\front\ProductOrder;
use App\Models\front\Review;
use App\Models\front\WithDraw;
use App\Models\OnlinePayment;
use App\Models\User;
use App\Services\Payments\PaymentGateway;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Tests\Fakes\FakePaymentGateway;
use Tests\TestCase;

/** Flows that wrote to columns no migration created (2026_10_20_100000_add_missing_legacy_columns). */
class LegacyColumnsTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_paid_digital_product_needs_no_address_and_keeps_the_phone(): void
    {
        Notification::fake();
        Mail::fake();
        $gateway = new FakePaymentGateway();
        $this->app->instance(PaymentGateway::class, $gateway);
        Setting::create(['website_commission' => '10']);
        $product = Product::create(['name' => 'كتاب رقمي', 'slug' => 'ebook', 'image' => 'x.png', 'price' => 80, 'type' => 'digital', 'digital_file' => 'ebook.pdf']);
        $buyer = User::factory()->create();

        $this->actingAs($buyer)->post('product_order', ['product_id' => $product->id, 'name' => 'سارة', 'phone' => '01000000001', 'email' => 's@example.com'])->assertRedirect();
        $payment = OnlinePayment::sole();
        $gateway->markPaid((string) $payment->id);
        $this->call('POST', route('easykash.webhook'), ['customerReference' => $payment->id, 'status' => 'PAID', 'signatureHash' => 'valid-signature'])->assertOk();

        $order = ProductOrder::sole();
        $this->assertSame(['01000000001', null], [$order->phone, $order->address]);
    }

    public function test_a_bank_withdrawal_is_saved(): void
    {
        $user = User::factory()->create(['balance' => 500]);

        $this->actingAs($user)->post('withdraw_balance', [
            'amount' => 200, 'method' => 'bank', 'country' => 'مصر', 'account_name' => 'سارة', 'account_number' => '123',
            'bank_name' => 'بنك مصر', 'iban_code' => 'EG00', 'swift_code' => 'BMISEGCX',
        ])->assertSessionHas('Success_message');

        $this->assertSame(['بنك مصر', 'EG00', null], [WithDraw::sole()->bank_name, WithDraw::sole()->iban_code, WithDraw::sole()->paypal_mail]);
        $this->assertEquals(300, $user->fresh()->balance);
    }

    public function test_a_consultation_chat_starts_without_a_service(): void
    {
        $consultant = User::factory()->create();

        $this->actingAs(User::factory()->create())->post('consult_conversation/start', ['consultant' => $consultant->id, 'category' => 3, 'question' => 'سؤال'])->assertRedirect();

        $conversation = Conversation::sole();
        $this->assertSame(['استشارة', $consultant->id, 3, null], [$conversation->chat_type, (int) $conversation->consultant_id, (int) $conversation->specialist_id, $conversation->service_id]);
    }

    public function test_a_visitor_review_waits_for_approval(): void
    {
        $this->post(route('front.reviews.post'), ['name' => 'أحمد', 'rating' => 5, 'notes' => 'خدمة ممتازة'])->assertSessionHas('Success_message');

        $review = Review::sole();
        $this->assertSame(['أحمد', 5, 'خدمة ممتازة', false], [$review->client_name, $review->rating, $review->content, $review->is_approved]);
    }
}
