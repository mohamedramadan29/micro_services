<?php

namespace Tests\Feature;

use App\Models\admin\Category;
use App\Models\admin\Setting;
use App\Models\admin\SubCategory;
use App\Models\RequestOffer;
use App\Models\RequestPayment;
use App\Models\ServiceRequest;
use App\Models\User;
use App\Models\WalletTransaction;
use App\Notifications\NewInquiryNotification;
use App\Notifications\NoOffersAlertNotification;
use App\Notifications\PaymentConflictNotification;
use App\Notifications\RequestClosedNotification;
use App\Services\Payments\PaymentGateway;
use App\Services\Payments\RequestPaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\Fakes\FakePaymentGateway;
use Tests\TestCase;

class AdminRequestToolsTest extends TestCase
{
    use RefreshDatabase;

    private FakePaymentGateway $gateway;
    private User $admin;
    private User $client;
    private User $freelancer;
    private SubCategory $service;

    protected function setUp(): void
    {
        parent::setUp();
        Notification::fake();
        $this->gateway = new FakePaymentGateway();
        $this->app->instance(PaymentGateway::class, $this->gateway);
        Setting::create(['website_commission' => '10', 'website_balance' => 0]);

        $category = Category::create(['name' => 'تسويق', 'slug' => 'm', 'image' => 'x.png', 'status' => 1]);
        $this->service = SubCategory::create(['name' => 'إعلانات المواقع', 'slug' => 'ads', 'image' => 'x.png', 'parent_id' => $category->id, 'status' => 1]);
        $this->admin = User::factory()->admin()->create();
        $this->client = User::factory()->create();
        $this->freelancer = User::factory()->freelancer()->create();
        $this->freelancer->specializations()->attach($this->service->id);
    }

    private function request(array $attributes = []): ServiceRequest
    {
        $request = ServiceRequest::create($attributes + [
            'user_id' => $this->client->id, 'category_id' => $this->service->parent_id, 'sub_category_id' => $this->service->id,
            'category_name' => 'تسويق', 'service_name' => 'إعلانات المواقع', 'path' => 'execute', 'status' => ServiceRequest::STATUS_OPEN,
            'problem' => 'حملة', 'contact_name' => 'سارة', 'contact_phone' => '0100', 'contact_email' => 'sara@example.com', 'published_at' => now(),
        ]);
        $request->update(['number' => 'REQ-' . (1000 + $request->id)]);

        return $request;
    }

    private function offer(ServiceRequest $request, array $attributes = []): RequestOffer
    {
        return $request->offers()->create($attributes + [
            'freelancer_id' => $this->freelancer->id, 'price' => 200, 'duration_days' => 5, 'scope' => str_repeat('نطاق ', 5), 'message' => str_repeat('رسالة ', 5),
        ]);
    }

    /** Request with a paid (held) offer. */
    private function paidRequest(): ServiceRequest
    {
        $request = $this->request();
        $offer = $this->offer($request);
        $service = $this->app->make(RequestPaymentService::class);
        $service->startCheckout($offer);
        $service->fulfill($this->gateway->markPaid(array_key_last($this->gateway->sessions)));

        return $request->refresh();
    }

    public function test_no_offer_alert_is_sent_once_after_an_hour_and_only_for_published_requests(): void
    {
        $late = $this->request(['published_at' => now()->subMinutes(61)]);
        $fresh = $this->request(['published_at' => now()->subMinutes(30)]);
        $answered = $this->request(['published_at' => now()->subMinutes(90)]);
        $this->offer($answered);
        $inquiry = $this->request(['status' => ServiceRequest::STATUS_INQUIRY, 'published_at' => null, 'created_at' => now()->subHours(3)]);

        $this->artisan('requests:alert-no-offers')->assertSuccessful();
        $this->artisan('requests:alert-no-offers')->assertSuccessful();

        Notification::assertSentToTimes($this->admin, NoOffersAlertNotification::class, 1);
        $this->assertNotNull($late->fresh()->no_offer_alerted_at);
        $this->assertSame(ServiceRequest::FOLLOWUP_NEW, $late->fresh()->followup_status);
        foreach ([$fresh, $answered, $inquiry] as $request) {
            $this->assertNull($request->fresh()->no_offer_alerted_at);
        }
    }

    public function test_an_offer_after_the_alert_resolves_the_followup(): void
    {
        $request = $this->request(['published_at' => now()->subHours(2)]);
        $this->artisan('requests:alert-no-offers');

        $this->actingAs($this->freelancer)->post(route('freelancer.offers.save', $request), [
            'price' => 300, 'duration_days' => 5, 'scope' => str_repeat('نطاق العمل ', 3), 'message' => str_repeat('رسالة العرض ', 3),
        ])->assertSessionHasNoErrors();

        $this->assertSame(ServiceRequest::FOLLOWUP_RESOLVED, $request->fresh()->followup_status);
    }

    public function test_admins_follow_up_an_alert(): void
    {
        $request = $this->request(['published_at' => now()->subHours(2)]);
        $this->artisan('requests:alert-no-offers');

        $this->actingAs($this->admin)->get(route('admin.requests.index', ['tab' => 'followup']))->assertOk()->assertSee($request->number);
        $this->actingAs($this->admin)->post(route('admin.requests.followup', $request), ['followup_status' => 'in_progress', 'followup_note' => 'تواصلت مع مستقلين اثنين'])
            ->assertSessionHas('success');
        $this->assertSame('in_progress', $request->fresh()->followup_status);
        $this->assertSame('تواصلت مع مستقلين اثنين', $request->fresh()->followup_note);
    }

    public function test_new_inquiries_alert_the_admins(): void
    {
        \App\Models\ServiceForm::create(['sub_category_id' => $this->service->id, 'status' => \App\Models\ServiceForm::STATUS_PUBLISHED])
            ->sections()->create(['title' => 'تفاصيل', 'sort_order' => 1]);
        $payload = [
            'sub_category_id' => $this->service->id, 'problem' => 'أريد معرفة تكلفة حملة شهرية',
            'contact_name' => 'سارة', 'contact_phone' => '01000000000', 'contact_email' => 'sara@example.com',
        ];

        $this->actingAs($this->client)->post(route('requests.store'), $payload + ['action' => 'explore'])->assertSessionHasNoErrors();
        Notification::assertSentTo($this->admin, NewInquiryNotification::class);

        $this->actingAs($this->client)->post(route('requests.store'), $payload + ['action' => 'execute']);
        Notification::assertSentToTimes($this->admin, NewInquiryNotification::class, 1);
    }

    public function test_lists_and_indicators(): void
    {
        $open = $this->request();
        $inquiry = $this->request(['status' => ServiceRequest::STATUS_INQUIRY, 'contact_name' => 'خالد']);
        $closed = $this->request(['status' => ServiceRequest::STATUS_NOT_COMPLETED, 'closed_reason' => 'price_not_agreed', 'closed_at' => now()]);

        $this->actingAs(User::factory()->create())->get(route('admin.requests.index'))->assertForbidden();

        $this->actingAs($this->admin)->get(route('admin.requests.index'))
            ->assertOk()->assertSee($open->number)->assertDontSee($inquiry->number)->assertDontSee($closed->number);
        $this->actingAs($this->admin)->get(route('admin.requests.index', ['tab' => 'inquiries']))
            ->assertSee($inquiry->number)->assertDontSee($open->number);
        $this->actingAs($this->admin)->get(route('admin.requests.index', ['tab' => 'not_completed', 'reason' => 'price_not_agreed']))
            ->assertSee($closed->number)->assertSee('لم يتم الاتفاق على السعر');
        $this->actingAs($this->admin)->get(route('admin.requests.index', ['tab' => 'inquiries', 'q' => 'خالد']))->assertSee($inquiry->number);

        $this->actingAs($this->admin)->get(route('admin.requests.show', $open))
            ->assertOk()->assertSee('sara@example.com')->assertSee('نقل إلى «لم يتم التنفيذ»');
    }

    public function test_admin_publishes_an_inquiry_only_with_client_consent(): void
    {
        $inquiry = $this->request(['status' => ServiceRequest::STATUS_INQUIRY, 'path' => 'explore', 'published_at' => null]);

        $this->actingAs($this->admin)->post(route('admin.requests.publish', $inquiry))->assertSessionHasErrors('client_agreed');
        $this->assertTrue($inquiry->fresh()->isInquiry());

        $this->actingAs($this->admin)->post(route('admin.requests.publish', $inquiry), ['client_agreed' => 1])->assertSessionHas('success');
        $this->assertSame(ServiceRequest::STATUS_OPEN, $inquiry->fresh()->status);
        $this->assertNotNull($inquiry->fresh()->published_at);
    }

    public function test_closing_an_open_request_records_the_reason_and_closes_offers(): void
    {
        $request = $this->request();
        $offer = $this->offer($request);

        $this->actingAs($this->admin)->post(route('admin.requests.close', $request), ['reason' => 'no_offer_chosen', 'note' => 'العميل لم يرد'])
            ->assertSessionHas('success');

        $request->refresh();
        $this->assertSame(ServiceRequest::STATUS_NOT_COMPLETED, $request->status);
        $this->assertSame('no_offer_chosen', $request->closed_reason);
        $this->assertSame($this->admin->id, $request->closed_by);
        $this->assertSame(RequestOffer::STATUS_REJECTED, $offer->fresh()->status);
        Notification::assertSentTo($this->client, RequestClosedNotification::class);

        $this->actingAs($this->admin)->post(route('admin.requests.close', $request), ['reason' => 'other'])->assertSessionHasErrors();
        // «سبب آخر» needs a written reason (audit 2026-10-16).
        $other = $this->request();
        $this->actingAs($this->admin)->post(route('admin.requests.close', $other), ['reason' => 'other', 'note' => ''])->assertSessionHasErrors('note');
        $this->actingAs($this->admin)->post(route('admin.requests.close', $other), ['reason' => 'other', 'note' => 'العميل غيّر خطته'])->assertSessionHas('success');
    }

    public function test_closing_a_paid_request_refunds_the_client_first(): void
    {
        $request = $this->paidRequest();

        $this->gateway->failRefunds = true;
        $this->actingAs($this->admin)->post(route('admin.requests.close', $request), ['reason' => 'freelancer_withdrew'])->assertSessionHasErrors();
        $this->assertTrue($request->fresh()->isInProgress());
        $this->assertSame(RequestPayment::STATUS_PAID, RequestPayment::first()->status);

        $this->gateway->failRefunds = false;
        $this->actingAs($this->admin)->post(route('admin.requests.close', $request), ['reason' => 'freelancer_withdrew'])->assertSessionHas('success');
        $this->assertSame(ServiceRequest::STATUS_NOT_COMPLETED, $request->fresh()->status);
        $this->assertSame(RequestPayment::STATUS_REFUNDED, RequestPayment::first()->status);
        $this->assertSame(1, WalletTransaction::where('type', WalletTransaction::TYPE_REFUND)->count());
        $this->assertEquals(0, $this->freelancer->fresh()->balance);

        $this->actingAs($this->client)->get(route('requests.show', $request))->assertSee('تم استرجاع المبلغ');
        $this->actingAs($this->freelancer)->get(route('freelancer.requests.show', $request))->assertSee('أُغلق هذا الطلب دون تنفيذ')->assertDontSee('تسليم العمل');
    }

    public function test_admin_completes_on_behalf_of_the_client_with_a_note(): void
    {
        $request = $this->paidRequest();

        $this->actingAs($this->admin)->post(route('admin.requests.complete', $request), [])->assertSessionHasErrors('note');
        $this->actingAs($this->admin)->post(route('admin.requests.complete', $request), ['note' => 'أكد العميل هاتفيًا'])->assertSessionHas('success');

        $this->assertSame(ServiceRequest::STATUS_COMPLETED, $request->fresh()->status);
        $this->assertEquals(180, $this->freelancer->fresh()->balance);
        $this->assertStringContainsString('أكد العميل هاتفيًا', $request->fresh()->admin_note);
    }

    public function test_conflicting_payments_alert_admins_and_can_be_refunded(): void
    {
        $request = $this->request();
        $first = $this->offer($request);
        $second = $this->offer($request, ['freelancer_id' => User::factory()->freelancer()->create()->id]);
        $service = $this->app->make(RequestPaymentService::class);
        $service->startCheckout($first);
        $firstSession = array_key_last($this->gateway->sessions);
        $service->startCheckout($second);
        $service->fulfill($this->gateway->markPaid(array_key_last($this->gateway->sessions)));
        $service->fulfill($this->gateway->markPaid($firstSession));

        $conflict = RequestPayment::where('status', RequestPayment::STATUS_CONFLICT)->firstOrFail();
        Notification::assertSentTo($this->admin, PaymentConflictNotification::class);

        $this->actingAs($this->admin)->get(route('admin.requests.index', ['tab' => 'refunds']))->assertSee($request->number);
        $this->actingAs($this->admin)->post(route('admin.requests.refund', $conflict))->assertSessionHas('success');
        $this->assertSame(RequestPayment::STATUS_REFUNDED, $conflict->fresh()->status);
        $this->assertTrue($request->fresh()->isInProgress()); // the winning payment is untouched
    }

    public function test_client_can_cancel_before_choosing_an_offer_only(): void
    {
        $request = $this->request();
        $this->actingAs($this->freelancer)->post(route('requests.cancel', $request))->assertForbidden();
        $this->actingAs($this->client)->post(route('requests.cancel', $request), ['note' => 'لم أعد أحتاجه'])->assertSessionHas('Success_message');
        $this->assertSame('client_cancelled', $request->fresh()->closed_reason);

        $paid = $this->paidRequest();
        $this->actingAs($this->client)->post(route('requests.cancel', $paid))->assertSessionHasErrors();
        $this->assertTrue($paid->fresh()->isInProgress());
    }

    public function test_admin_bell_lists_request_notifications(): void
    {
        Notification::swap(new \Illuminate\Notifications\ChannelManager($this->app));
        $request = $this->request();
        $this->admin->notify(new NoOffersAlertNotification($request));

        $this->actingAs($this->admin)->get(route('admin.requests.index'))->assertSee('تنبيه: لا توجد عروض بعد ساعة على الطلب ' . $request->number);
        $this->actingAs($this->admin)->post(route('admin.notifications.read-all'));
        $this->assertSame(0, $this->admin->fresh()->unreadNotifications()->count());
    }

    public function test_admin_guide_is_available_to_admins_only(): void
    {
        $this->actingAs($this->admin)->get(route('admin.requests.guide'))->assertOk()->assertSee('إضافة خدمة وأسئلتها')->assertSee(route('admin.request-forms.index'), false);
        $this->actingAs($this->client)->get(route('admin.requests.guide'))->assertForbidden();
    }

    public function test_easykash_refunds_are_recorded_as_manual(): void
    {
        $request = $this->paidRequest();
        $this->gateway->manualRefunds = true;

        $this->actingAs($this->admin)->post(route('admin.requests.close', $request), ['reason' => 'not_feasible'])->assertSessionHas('success');

        $payment = RequestPayment::first();
        $this->assertSame(RequestPayment::STATUS_REFUNDED, $payment->status);
        $this->assertSame('manual', $payment->refund_reference);
        $this->assertStringContainsString('لوحة EasyKash', WalletTransaction::where('type', WalletTransaction::TYPE_REFUND)->value('description'));
    }
}
