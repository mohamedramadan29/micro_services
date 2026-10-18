<?php

namespace Tests\Feature;

use App\Models\admin\Service;
use App\Models\admin\Setting;
use App\Models\front\Cart;
use App\Models\front\Order;
use App\Models\front\OrderDetail;
use App\Models\OnlinePayment;
use App\Models\User;
use App\Notifications\NewOrderNotification;
use App\Services\Payments\PaymentGateway;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\Fakes\FakePaymentGateway;
use Tests\TestCase;

/** The cart is paid through EasyKash like every other purchase (it used to create orders without any payment). */
class CartCheckoutTest extends TestCase
{
    use RefreshDatabase;

    private FakePaymentGateway $gateway;
    private User $buyer;
    private User $sellerA;
    private User $sellerB;
    private Service $logo;
    private Service $site;

    protected function setUp(): void
    {
        parent::setUp();
        Notification::fake();
        $this->gateway = new FakePaymentGateway();
        $this->app->instance(PaymentGateway::class, $this->gateway);
        Setting::create(['website_commission' => '10']);
        $this->buyer = User::factory()->create();
        $this->sellerA = User::factory()->freelancer()->create();
        $this->sellerB = User::factory()->freelancer()->create();
        $service = fn (User $seller, string $name, $price) => Service::create(['name' => $name, 'slug' => $name, 'cat_id' => 1, 'user_id' => $seller->id, 'image' => 'x.png', 'description' => 'd', 'tags' => 't', 'price' => $price, 'status' => 1]);
        $this->logo = $service($this->sellerA, 'تصميم شعار', '300');
        $this->site = $service($this->sellerB, 'موقع تعريفي', '1200');
    }

    private function fillCart(): void
    {
        // A tampered form: the price and seller in the request are ignored.
        $this->actingAs($this->buyer)->post('cart/add', ['service_id' => $this->logo->id, 'service_name' => 'x', 'service_price' => 1, 'user_serv' => $this->buyer->id, 'qty' => 9])->assertSessionHas('Success_message');
        $this->actingAs($this->buyer)->post('cart/add', ['service_id' => $this->site->id]);
    }

    public function test_cart_items_take_name_price_and_seller_from_the_service(): void
    {
        $this->fillCart();

        $item = Cart::where('service_id', $this->logo->id)->sole();
        $this->assertSame(['تصميم شعار', $this->sellerA->id, 1], [$item->service_name, (int) $item->user_serv, (int) $item->quantity]);
        $this->assertEquals(300, $item->price);

        $this->actingAs($this->sellerA)->post('cart/add', ['service_id' => $this->logo->id])->assertSessionHasErrors();
    }

    public function test_the_whole_cart_is_paid_then_ordered_and_emptied(): void
    {
        $this->fillCart();
        // A later price change applies: the total is always taken from the services.
        $this->site->update(['price' => '1000']);

        $this->actingAs($this->buyer)->get('cart')->assertOk()->assertSee('1,300 ج.م')->assertDontSee('الرسوم');
        $this->actingAs($this->buyer)->get('checkout')->assertOk()->assertSee('1,300 ج.م')->assertSee('ادفع واتمم الطلب')->assertDontSee('PayPal');

        $this->actingAs($this->buyer)->post('checkout/order')->assertRedirect('https://pay.test/1');
        $payment = OnlinePayment::sole();
        $this->assertSame(OnlinePayment::PURPOSE_SERVICE_ORDER, $payment->purpose);
        $this->assertEquals(1300, $payment->amount);
        $this->assertSame(0, Order::count());
        $this->assertSame(2, Cart::count());

        $this->gateway->markPaid((string) $payment->id);
        $this->call('POST', route('easykash.webhook'), ['customerReference' => $payment->id, 'status' => 'PAID', 'signatureHash' => 'valid-signature'])->assertOk();
        $this->call('POST', route('easykash.webhook'), ['customerReference' => $payment->id, 'status' => 'PAID', 'signatureHash' => 'valid-signature'])->assertOk();

        $order = Order::sole();
        $this->assertNull($order->user_seller);
        $this->assertEquals([1300, 130, 1170], [$order->order_price, $order->website_commission, $order->seller_commission]);
        $lines = OrderDetail::orderBy('id')->get();
        $this->assertSame([$this->sellerA->id, $this->sellerB->id], $lines->pluck('user_seller')->map(fn ($id) => (int) $id)->all());
        $this->assertEquals([30, 100], $lines->pluck('website_commission')->map(fn ($v) => (float) $v)->all());
        $this->assertSame(0, Cart::count());
        Notification::assertSentToTimes($this->sellerA, NewOrderNotification::class, 1);
        Notification::assertSentToTimes($this->sellerB, NewOrderNotification::class, 1);
    }

    public function test_an_unpaid_cart_creates_nothing_and_keeps_the_items(): void
    {
        $this->fillCart();
        $this->actingAs($this->buyer)->post('checkout/order');
        $payment = OnlinePayment::sole();
        $this->gateway->markFailed((string) $payment->id);

        $this->actingAs($this->buyer)->get(route('payments.return', ['onlinePayment' => $payment->id, 'token' => $payment->return_token]))
            ->assertRedirect(url('checkout'))->assertSessionHasErrors();
        $this->assertSame(0, Order::count());
        $this->assertSame(2, Cart::count());
    }

    public function test_an_empty_cart_cannot_be_checked_out(): void
    {
        $this->actingAs($this->buyer)->get('checkout')->assertRedirect('cart');
        $this->actingAs($this->buyer)->post('checkout/order')->assertRedirect('cart');
        $this->assertSame(0, OnlinePayment::count());
    }

    public function test_a_cart_item_can_only_be_deleted_by_its_owner(): void
    {
        $this->fillCart();
        $item = Cart::first();

        $this->actingAs(User::factory()->create())->post('cart/delete', ['cartId' => $item->id]);
        $this->assertNotNull($item->fresh());

        $this->actingAs($this->buyer)->post('cart/delete', ['cartId' => $item->id]);
        $this->assertNull($item->fresh());
    }
}
