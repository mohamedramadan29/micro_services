<?php

namespace Tests\Feature;

use App\Models\admin\Category;
use App\Models\admin\Setting;
use App\Models\admin\SubCategory;
use App\Models\RequestOffer;
use App\Models\ServiceRequest;
use App\Models\ServiceRequestEvent;
use App\Models\User;
use App\Notifications\NoOffersAlertNotification;
use App\Notifications\RequestUpdateNotification;
use App\Services\Payments\PaymentGateway;
use App\Services\Payments\RequestPaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\Fakes\FakePaymentGateway;
use Tests\TestCase;

/** Gaps of «رسم مسار العميل والأدمن والمستقلين»: history, invitations, tabs and filters. */
class RequestHistoryAndInvitesTest extends TestCase
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
        $this->admin = User::factory()->admin()->create(['name' => 'مدير الموقع']);
        $this->client = User::factory()->create();
        $this->freelancer = User::factory()->freelancer()->create(['name' => 'منى المستقلة']);
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

    private function offer(ServiceRequest $request, User $freelancer, array $attributes = []): RequestOffer
    {
        return $request->offers()->create($attributes + [
            'freelancer_id' => $freelancer->id, 'price' => 200, 'duration_days' => 5, 'scope' => str_repeat('نطاق ', 5), 'message' => str_repeat('رسالة ', 5),
        ]);
    }

    public function test_every_status_change_is_logged_with_who_did_it(): void
    {
        $request = $this->request(['path' => 'explore', 'status' => ServiceRequest::STATUS_INQUIRY, 'published_at' => null]);

        $this->actingAs($this->admin)->post(route('admin.requests.publish', $request), ['client_agreed' => 1])->assertSessionHas('success');
        $this->actingAs($this->admin)->post(route('admin.requests.close', $request), ['reason' => 'no_offers', 'note' => 'لم يستجب أحد']);

        $events = $request->events()->get()->reverse()->values();
        $this->assertSame(['created', 'status', 'note', 'status'], $events->pluck('type')->all());
        $this->assertSame([null, 'inquiry', null, 'open'], $events->pluck('from_status')->all());
        $this->assertSame(['inquiry', 'open', null, 'not_completed'], $events->pluck('to_status')->all());
        $this->assertSame('لم تصل عروض مناسبة — لم يستجب أحد', $events[3]->note);
        $this->assertSame($this->admin->id, $events[3]->user_id);

        $this->actingAs($this->admin)->get(route('admin.requests.show', $request->fresh()))->assertOk()
            ->assertSee('سجل الطلب')
            ->assertSeeInOrder(['تغيّرت الحالة من «مفتوح للعروض» إلى «لم يتم التنفيذ»', 'مدير الموقع'])
            ->assertSee('أُرسل الطلب بحالة «استفسار أسعار»');
    }

    public function test_a_status_changed_by_a_payment_is_logged_as_the_system(): void
    {
        $request = $this->request();
        $offer = $this->offer($request, $this->freelancer);
        $payments = $this->app->make(RequestPaymentService::class);
        $payments->startCheckout($offer);
        $payments->fulfill($this->gateway->markPaid(array_key_last($this->gateway->sessions)));

        $event = $request->events()->where('type', ServiceRequestEvent::TYPE_STATUS)->sole();
        $this->assertSame(['open', 'in_progress', null], [$event->from_status, $event->to_status, $event->user_id]);
    }

    public function test_followup_updates_are_logged(): void
    {
        $request = $this->request(['no_offer_alerted_at' => now(), 'followup_status' => ServiceRequest::FOLLOWUP_NEW]);

        $this->actingAs($this->admin)->post(route('admin.requests.followup', $request), ['followup_status' => 'in_progress', 'followup_note' => 'اتصلت بمستقلين']);

        $event = $request->events()->where('type', ServiceRequestEvent::TYPE_FOLLOWUP)->sole();
        $this->assertSame(['in_progress', 'اتصلت بمستقلين'], [$event->to_status, $event->note]);
    }

    public function test_admin_sees_matching_freelancers_and_invites_each_once(): void
    {
        $request = $this->request();
        $offered = User::factory()->freelancer()->create(['name' => 'خالد أرسل عرضًا']);
        $offered->specializations()->attach($this->service->id);
        $this->offer($request, $offered);
        $other = User::factory()->freelancer()->create(['name' => 'مستقل خدمة أخرى']);

        $this->actingAs($this->admin)->get(route('admin.requests.show', $request))->assertOk()
            ->assertSee('المستقلون المطابقون للخدمة (2)')->assertSee('منى المستقلة')->assertSee('أرسل عرضًا')
            ->assertDontSee('مستقل خدمة أخرى');

        $this->actingAs($this->client)->post(route('admin.requests.invite', [$request, $this->freelancer->id]))->assertForbidden();

        $this->actingAs($this->admin)->post(route('admin.requests.invite', [$request, $this->freelancer->id]))->assertSessionHas('success');
        Notification::assertSentTo($this->freelancer, RequestUpdateNotification::class, fn ($n) => str_contains($n->toArray($this->freelancer)['text'], 'يدعوك لإرسال عرض'));
        $this->actingAs($this->admin)->post(route('admin.requests.invite', [$request, $this->freelancer->id]))->assertSessionHasErrors();
        $this->actingAs($this->admin)->post(route('admin.requests.invite', [$request, $other->id]))->assertSessionHasErrors();
        Notification::assertSentToTimes($this->freelancer, RequestUpdateNotification::class, 1);

        $this->actingAs($this->admin)->get(route('admin.requests.show', $request))->assertSee('تمت دعوته')->assertSee('دعوة المستقل منى المستقلة لإرسال عرض');
    }

    public function test_closed_requests_cannot_be_used_for_invitations(): void
    {
        $request = $this->request(['status' => ServiceRequest::STATUS_NOT_COMPLETED]);

        $this->actingAs($this->admin)->post(route('admin.requests.invite', [$request, $this->freelancer->id]))->assertSessionHasErrors();
        $this->assertSame(0, ServiceRequestEvent::where('type', ServiceRequestEvent::TYPE_INVITED)->count());
    }

    public function test_admin_can_filter_requests_by_the_chosen_freelancer(): void
    {
        $mine = $this->request(['status' => ServiceRequest::STATUS_IN_PROGRESS]);
        $mine->update(['accepted_offer_id' => $this->offer($mine, $this->freelancer, ['status' => RequestOffer::STATUS_ACCEPTED])->id]);
        $others = $this->request(['status' => ServiceRequest::STATUS_IN_PROGRESS]);
        $someone = User::factory()->freelancer()->create(['name' => 'شخص آخر']);
        $others->update(['accepted_offer_id' => $this->offer($others, $someone, ['status' => RequestOffer::STATUS_ACCEPTED])->id]);

        $this->actingAs($this->admin)->get(route('admin.requests.index', ['tab' => 'active', 'freelancer' => 'منى']))
            ->assertSee($mine->number)->assertDontSee($others->number);
    }

    public function test_client_offer_tabs_include_in_discussion_and_not_selected(): void
    {
        $request = $this->request();
        $talking = User::factory()->freelancer()->create();
        $this->offer($request, $talking, ['status' => RequestOffer::STATUS_IN_DISCUSSION]);
        $this->offer($request, $this->freelancer, ['status' => RequestOffer::STATUS_REJECTED]);

        $this->actingAs($this->client)->get(route('requests.show', $request))->assertOk()
            ->assertSee('قيد النقاش 1')->assertSee('لم يتم اختيارها 1');
        $this->actingAs($this->client)->get(route('requests.show', [$request, 'tab' => 'rejected']))->assertOk()->assertSee($this->freelancer->name);
    }

    public function test_no_offer_alert_in_the_bell_names_the_client_and_publish_time(): void
    {
        $request = $this->request(['published_at' => now()->subHours(2)]);

        $data = (new NoOffersAlertNotification($request))->toArray($this->admin);

        $this->assertStringContainsString('العميل: سارة', $data['details']);
        $this->assertStringContainsString('نُشر ' . $request->published_at->format('Y-m-d H:i'), $data['details']);
    }

    public function test_clients_list_can_be_filtered_by_service(): void
    {
        $this->client->update(['name' => 'عميلة الإعلانات']);
        $this->request();
        $design = SubCategory::create(['name' => 'تصميم', 'slug' => 'design', 'image' => 'x.png', 'parent_id' => $this->service->parent_id, 'status' => 1]);
        $other = User::factory()->create(['name' => 'عميل التصميم']);
        $this->request(['user_id' => $other->id, 'sub_category_id' => $design->id, 'service_name' => 'تصميم']);

        $this->actingAs($this->admin)->get(route('admin.clients.index', ['service' => $this->service->id]))->assertOk()
            ->assertSee('عميلة الإعلانات')->assertDontSee('عميل التصميم');
    }
}
