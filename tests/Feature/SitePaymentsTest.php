<?php

namespace Tests\Feature;

use App\Models\admin\Package;
use App\Models\admin\PackageSubscribe;
use App\Models\admin\Product;
use App\Models\admin\Service;
use App\Models\admin\Setting;
use App\Models\front\Course;
use App\Models\front\CourseRegister;
use App\Models\front\Order;
use App\Models\front\OrderDetail;
use App\Models\front\PaymentTransaction;
use App\Models\front\ProductOrder;
use App\Models\OnlinePayment;
use App\Models\User;
use App\Notifications\NewOrderNotification;
use App\Services\Payments\PaymentGateway;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\Fakes\FakePaymentGateway;
use Tests\TestCase;

/** The direct purchases that used Stripe now go through EasyKash (one account, one webhook). */
class SitePaymentsTest extends TestCase
{
    use RefreshDatabase;

    private FakePaymentGateway $gateway;
    private User $buyer;

    protected function setUp(): void
    {
        parent::setUp();
        Notification::fake();
        $this->gateway = new FakePaymentGateway();
        $this->app->instance(PaymentGateway::class, $this->gateway);
        Setting::create(['website_commission' => '10', 'website_balance' => 0]);
        $this->buyer = User::factory()->create(['balance' => 0]);
    }

    private function latest(): OnlinePayment
    {
        return OnlinePayment::latest('id')->firstOrFail();
    }

    private function webhook(OnlinePayment $payment)
    {
        return $this->call('POST', route('easykash.webhook'), ['customerReference' => $payment->id, 'status' => 'PAID', 'signatureHash' => 'valid-signature']);
    }

    private function returnFrom(OnlinePayment $payment)
    {
        return $this->actingAs($this->buyer)->get(route('payments.return', ['onlinePayment' => $payment->id, 'token' => $payment->return_token, 'status' => 'PAID']));
    }

    public function test_ordering_a_service_creates_the_order_only_after_easykash_confirms(): void
    {
        $seller = User::factory()->freelancer()->create();
        $service = Service::create(['name' => 'تصميم شعار', 'slug' => 'logo', 'cat_id' => 1, 'user_id' => $seller->id, 'image' => 'x.png', 'description' => 'd', 'tags' => 't', 'price' => '300', 'status' => 1]);

        $this->actingAs($this->buyer)->post('create_order', ['service_id' => $service->id])->assertRedirect('https://pay.test/1');
        $payment = $this->latest();
        $this->assertSame([OnlinePayment::PURPOSE_SERVICE_ORDER, 'EGP', OnlinePayment::STATUS_PENDING], [$payment->purpose, $payment->currency, $payment->status]);
        $this->assertEquals(300, $payment->amount);
        $this->assertSame(0, Order::count());

        $this->gateway->markPaid((string) $payment->id);
        $this->webhook($payment)->assertOk();
        $this->webhook($payment)->assertOk();
        $this->artisan('payments:sync');

        $order = Order::sole();
        $this->assertEquals([300, 30, 270], [$order->order_price, $order->website_commission, $order->seller_commission]);
        $this->assertSame([$this->buyer->id, $seller->id], [(int) $order->user_buyer, (int) $order->user_seller]);
        $this->assertSame('تصميم شعار', OrderDetail::sole()->service_name);
        Notification::assertSentToTimes($seller, NewOrderNotification::class, 1);

        $this->returnFrom($payment)->assertRedirect(url('purches'))->assertSessionHas('Success_message', 'تم طلب الخدمة بنجاح');

        $this->actingAs($seller)->post('create_order', ['service_id' => $service->id])->assertSessionHasErrors();
    }

    public function test_buying_a_product_records_the_paid_order(): void
    {
        $product = Product::create(['name' => 'كتاب', 'slug' => 'book', 'image' => 'x.png', 'price' => 200, 'discount' => 150]);
        $form = ['product_id' => $product->id, 'name' => 'سارة', 'phone' => '01000000001', 'email' => 's@example.com', 'country' => 'مصر', 'city' => 'القاهرة', 'address' => 'شارع 1'];

        $this->post('product_order', $form)->assertRedirect(route('user_login'));

        $this->actingAs($this->buyer)->post('product_order', $form)->assertRedirect();
        $payment = $this->latest();
        $this->assertEquals(150, $payment->amount);
        $this->assertSame(['سارة', 's@example.com', '01000000001'], [$payment->contact_name, $payment->contact_email, $payment->contact_mobile]);

        $this->gateway->markPaid((string) $payment->id);
        $this->returnFrom($payment)->assertRedirect(route('user.products.purches'));

        $order = ProductOrder::sole();
        $this->assertSame(['تم الدفع', 'القاهرة', $this->buyer->id], [$order->order_status, $order->city, (int) $order->user_id]);
        $this->assertEquals(150, $order->price);
    }

    public function test_package_subscription_is_settled_by_the_sync_command(): void
    {
        $package = Package::create(['name' => 'الباقة الذهبية', 'slug' => 'gold', 'description' => 'd', 'price' => 999]);

        $this->actingAs($this->buyer)->post(route('subscribe.plan', $package->id))->assertRedirect();
        $this->gateway->markPaid((string) $this->latest()->id);
        $this->artisan('payments:sync')->expectsOutputToContain('"paid":1');

        $subscribe = PackageSubscribe::sole();
        $this->assertSame(['الباقة الذهبية', $this->buyer->name], [$subscribe->package_name, $subscribe->user_name]);
        $this->assertEquals(999, $subscribe->price);
    }

    public function test_course_registration_pays_the_owner_minus_the_commission(): void
    {
        $owner = User::factory()->create(['balance' => 5]);
        $course = Course::create(['user_id' => $owner->id, 'title' => 'دورة تسويق', 'image' => 'x.png', 'desc' => 'd', 'leason_numbers' => 4, 'price' => 500]);

        $this->actingAs($this->buyer)->post('course_regitser/' . $course->id)->assertRedirect();
        $payment = $this->latest();
        // The commission is fixed at checkout time.
        Setting::first()->update(['website_commission' => '50']);
        $this->gateway->markPaid((string) $payment->id);
        $this->webhook($payment)->assertOk();

        $register = CourseRegister::sole();
        $this->assertEquals([450, 50], [$register->course_owner_profit, $register->website_profit]);
        $this->assertEquals(455, $owner->fresh()->balance);
        $this->assertEquals(50, Setting::first()->website_balance);
        $this->assertSame(1, $course->fresh()->current_student_num);

        $this->actingAs($this->buyer)->post('course_regitser/' . $course->id)->assertSessionHasErrors();
        $this->assertSame(1, OnlinePayment::count());
    }

    public function test_balance_top_up_is_credited_in_full_once_paid(): void
    {
        $this->actingAs($this->buyer)->get(route('user_balance'))->assertOk()
            ->assertSee('متابعة للدفع')->assertSee('ج.م')->assertDontSee('checkout.stripe.com');

        $this->actingAs($this->buyer)->post('charge_balance', ['price' => 0])->assertSessionHasErrors('price');

        $this->actingAs($this->buyer)->post('charge_balance', ['price' => 250])->assertRedirect();
        $payment = $this->latest();

        // Still pending (e.g. Fawry voucher): nothing credited yet.
        $this->returnFrom($payment)->assertRedirect(route('user_balance'))->assertSessionHas('Success_message');
        $this->assertEquals(0, $this->buyer->fresh()->balance);

        $this->gateway->markPaid((string) $payment->id);
        $this->webhook($payment)->assertOk();
        $this->webhook($payment)->assertOk();

        $this->assertEquals(250, $this->buyer->fresh()->balance);
        $transaction = PaymentTransaction::sole();
        $this->assertSame(['EasyKash', 'EGP', 'ek_' . $payment->id], [$transaction->payment_method, $transaction->payment_currency, $transaction->transaction_id]);
    }

    public function test_failed_or_underpaid_payments_change_nothing(): void
    {
        $this->actingAs($this->buyer)->post('charge_balance', ['price' => 100])->assertRedirect();
        $failed = $this->latest();
        $this->gateway->markFailed((string) $failed->id);
        $this->returnFrom($failed)->assertRedirect(route('user_balance'))->assertSessionHasErrors();
        $this->assertSame(OnlinePayment::STATUS_FAILED, $failed->fresh()->status);

        $this->actingAs($this->buyer)->post('charge_balance', ['price' => 100])->assertRedirect();
        $underpaid = $this->latest();
        $this->gateway->markPaid((string) $underpaid->id, 10);
        $this->webhook($underpaid)->assertStatus(503);

        $this->assertEquals(0, $this->buyer->fresh()->balance);
        $this->assertSame(0, PaymentTransaction::count());
    }

    public function test_checkout_errors_are_shown_and_the_payment_is_cancelled(): void
    {
        config(['services.easykash.api_key' => null]);
        $this->app->forgetInstance(PaymentGateway::class);
        $this->app->bind(PaymentGateway::class, \App\Services\Payments\EasyKashPaymentGateway::class);

        $this->actingAs($this->buyer)->from(route('user_balance'))->post('charge_balance', ['price' => 100])
            ->assertRedirect(route('user_balance'))->assertSessionHasErrors();
        $this->assertSame(OnlinePayment::STATUS_CANCELLED, $this->latest()->status);
    }

    public function test_stripe_is_gone(): void
    {
        $this->assertFalse(class_exists(\Stripe\Stripe::class));
        $this->assertStringNotContainsStringIgnoringCase('stripe', file_get_contents(base_path('composer.json')));
    }

    public function test_a_webhook_cannot_mark_a_payment_paid_on_its_own_word(): void
    {
        $this->actingAs($this->buyer)->post('charge_balance', ['price' => 500])->assertRedirect();
        $payment = $this->latest();

        // Says PAID, but EasyKash reports the payment as still pending.
        $this->webhook($payment)->assertOk();

        $this->assertSame(OnlinePayment::STATUS_PENDING, $payment->fresh()->status);
        $this->assertEquals(0, $this->buyer->fresh()->balance);
    }

    public function test_the_real_webhook_works_without_an_hmac_secret_and_still_asks_easykash(): void
    {
        config(['services.easykash' => ['api_key' => 'ek_test_key', 'hmac_secret' => null, 'base_url' => 'https://easykash.test/api', 'currency' => 'EGP', 'payment_options' => '', 'cash_expiry_hours' => 24]]);
        $this->app->forgetInstance(PaymentGateway::class);
        $this->app->bind(PaymentGateway::class, \App\Services\Payments\EasyKashPaymentGateway::class);
        \Illuminate\Support\Facades\Http::fake([
            'easykash.test/api/directpayv1/pay' => \Illuminate\Support\Facades\Http::response(['redirectUrl' => 'https://pay.easykash.net/x']),
            'easykash.test/api/cash-api/inquire' => \Illuminate\Support\Facades\Http::sequence()
                ->push(['status' => 'PENDING'])
                ->push(['status' => 'PAID', 'easykashRef' => 'EK77', 'Amount' => '200']),
        ]);

        $this->actingAs($this->buyer)->post('charge_balance', ['price' => 200])->assertRedirect('https://pay.easykash.net/x');
        $payment = $this->latest();
        $forged = ['customerReference' => $payment->id, 'status' => 'PAID'];

        $this->call('POST', route('easykash.webhook'), $forged)->assertOk();
        $this->assertEquals(0, $this->buyer->fresh()->balance);

        $this->call('POST', route('easykash.webhook'), $forged)->assertOk();
        $this->assertEquals(200, $this->buyer->fresh()->balance);
        $this->assertSame('EK77', $payment->fresh()->gateway_transaction);
    }
}
