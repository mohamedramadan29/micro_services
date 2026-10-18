<?php

namespace Tests\Feature;

use App\Models\admin\Category;
use App\Models\admin\Setting;
use App\Models\admin\SubCategory;
use App\Models\Contract;
use App\Models\OnlinePayment;
use App\Models\RequestOffer;
use App\Models\ServiceForm;
use App\Models\ServiceFormQuestion as Q;
use App\Models\ServiceRequest;
use App\Models\User;
use App\Notifications\OfferNotSelectedNotification;
use App\Notifications\RequestUpdateNotification;
use App\Services\Payments\PaymentGateway;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\Fakes\FakePaymentGateway;
use Tests\TestCase;

/** Service 3, phase 1: posting a staged project and choosing a freelancer opens a contract (no payment yet). */
class StagedProjectTest extends TestCase
{
    use RefreshDatabase;

    private User $client;
    private SubCategory $service;
    private Q $goal;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('private_uploads');
        Notification::fake();
        $this->app->instance(PaymentGateway::class, new FakePaymentGateway());
        Setting::create(['website_commission' => '10']);

        $category = Category::create(['name' => 'برمجة', 'slug' => 'dev', 'image' => 'x.png', 'status' => 1]);
        $this->service = SubCategory::create(['name' => 'برمجة وتصميم مواقع إلكترونية', 'slug' => 'web', 'image' => 'x.png', 'parent_id' => $category->id, 'status' => 1]);
        $form = ServiceForm::create(['sub_category_id' => $this->service->id, 'status' => ServiceForm::STATUS_PUBLISHED]);
        $section = $form->sections()->create(['title' => 'تفاصيل', 'sort_order' => 1]);
        $this->goal = $form->questions()->create(['service_form_section_id' => $section->id, 'type' => Q::TYPE_SINGLE_CHOICE, 'label' => 'نوع الموقع', 'is_required' => true, 'options' => ['متجر', 'تعريفي'], 'sort_order' => 1]);
        $this->client = User::factory()->create();
    }

    private function payload(array $extra = []): array
    {
        return array_replace([
            'kind' => ServiceRequest::KIND_STAGED,
            'sub_category_id' => $this->service->id,
            'action' => ServiceRequest::PATH_EXECUTE,
            'project_title' => 'تطوير متجر إلكتروني',
            'answers' => [$this->goal->id => 'متجر'],
            'problem' => 'أريد متجرًا كاملًا بالدفع والشحن',
            'contact_name' => 'سارة', 'contact_phone' => '01000000000', 'contact_email' => 'sara@example.com',
        ], $extra);
    }

    private function postProject(array $extra = []): ServiceRequest
    {
        $this->actingAs($this->client)->post(route('requests.store'), $this->payload($extra))->assertSessionHasNoErrors();

        return ServiceRequest::latest('id')->firstOrFail();
    }

    private function offer(ServiceRequest $request, ?User $freelancer = null, float $price = 9000): RequestOffer
    {
        return $request->offers()->create([
            'freelancer_id' => ($freelancer ?? User::factory()->freelancer()->create())->id,
            'price' => $price, 'duration_days' => 30, 'scope' => str_repeat('نطاق العمل ', 3), 'message' => str_repeat('رسالة العرض ', 3),
        ]);
    }

    public function test_the_card_opens_the_staged_project_page_with_its_editable_texts(): void
    {
        $this->get('/')->assertOk();
        $card = \App\Models\ContentBlock::where('area', 'home.cards')->get()->first(fn ($b) => ($b->data['action'] ?? null) === 'staged');
        $this->get(route('home.card', $card->id))->assertRedirect(url('/staged-project'));
        $this->actingAs($this->client)->get(route('requests.staged'))->assertOk()
            ->assertSee('أنشئ مشروعًا بمراحل')->assertSee('كيف تعمل الخدمة؟')->assertSee('موّل المرحلة الأولى')
            ->assertSee('مشروع بدأ خارج نفّذها وتوقف مستقله؟')->assertSee('name="kind" value="staged"', false)
            ->assertSee('انشر المشروع لاستقبال العروض')->assertDontSee('بدي أشوف الأسعار');
        $this->actingAs($this->client)->get(route('requests.create'))->assertOk()->assertDontSee('name="kind" value="staged"', false);
    }

    public function test_a_staged_project_is_published_with_its_title_and_what_was_done_before(): void
    {
        // A title is required; a stopped project must say what was done.
        $this->actingAs($this->client)->post(route('requests.store'), $this->payload(['project_title' => '', 'stopped_project' => 1]))
            ->assertSessionHasErrors(['project_title', 'done_before']);

        // Even «explore» is published: the card has no price-inquiry path.
        $request = $this->postProject(['action' => ServiceRequest::PATH_EXPLORE, 'stopped_project' => 1, 'done_before' => 'صُممت الواجهات ولم تُبرمج صفحة الدفع']);
        $this->assertSame([ServiceRequest::KIND_STAGED, ServiceRequest::STATUS_OPEN, ServiceRequest::PATH_EXECUTE], [$request->kind, $request->status, $request->path]);
        $this->assertSame(['تطوير متجر إلكتروني', true, 'صُممت الواجهات ولم تُبرمج صفحة الدفع'], [$request->project_title, $request->stopped_project, $request->done_before]);

        $freelancer = User::factory()->freelancer()->create();
        $freelancer->specializations()->attach($this->service->id);
        $this->actingAs($freelancer)->get(route('freelancer.requests.index'))->assertOk()->assertSee('مشروع بمراحل')->assertSee('تطوير متجر إلكتروني');
        $this->actingAs($freelancer)->get(route('freelancer.requests.show', $request))->assertOk()
            ->assertSee('ما أُنجز قبل نفّذها')->assertSee('صُممت الواجهات')->assertDontSee('sara@example.com');

        // A normal request stays normal.
        $normal = $this->postProject(['kind' => null, 'project_title' => null]);
        $this->assertSame([ServiceRequest::KIND_STANDARD, null], [$normal->kind, $normal->project_title]);
    }

    public function test_choosing_a_freelancer_opens_a_contract_without_any_payment(): void
    {
        $request = $this->postProject();
        $chosen = $this->offer($request);
        $other = $this->offer($request, null, 7000);

        $this->actingAs($this->client)->get(route('requests.show', $request))->assertSee('اختيار المستقل والبدء في الاتفاق')->assertDontSee('اختيار العرض والدفع');
        $this->actingAs($this->client)->post(route('offers.accept', $chosen))
            ->assertRedirect(route('requests.show', $request))->assertSessionHas('Success_message');

        $this->assertSame(0, OnlinePayment::count());
        $request->refresh();
        $this->assertSame([ServiceRequest::STATUS_IN_PROGRESS, $chosen->id], [$request->status, $request->accepted_offer_id]);
        $this->assertEquals(9000, $request->agreed_price);
        $this->assertSame([RequestOffer::STATUS_ACCEPTED, RequestOffer::STATUS_REJECTED], [$chosen->fresh()->status, $other->fresh()->status]);

        $contract = Contract::sole();
        $this->assertSame([$request->id, $this->client->id, $chosen->freelancer_id, 'تطوير متجر إلكتروني', Contract::STATUS_AGREEMENT],
            [$contract->service_request_id, $contract->client_id, $contract->freelancer_id, $contract->title, $contract->status]);
        $this->assertSame('CNT-' . (1000 + $contract->id), $contract->number);
        Notification::assertSentTo($chosen->freelancer, RequestUpdateNotification::class, fn ($n) => str_contains($n->toArray($chosen->freelancer)['text'], 'اقتراح الاتفاق'));
        Notification::assertSentTo($other->freelancer, OfferNotSelectedNotification::class);

        $this->actingAs($this->client)->get(route('requests.show', $request))->assertOk()
            ->assertSee('الاتفاق على المراحل')->assertSee('لا تدفع شيئًا قبل الاتفاق')->assertDontSee('تأكيد استلام العمل');
        $this->actingAs($chosen->freelancer)->get(route('freelancer.requests.show', $request))->assertOk()
            ->assertSee('اختار العميل عرضك')->assertSee($contract->number)->assertDontSee('تسليم العمل');

        // Choosing again (or someone else) changes nothing.
        $this->actingAs($this->client)->post(route('offers.accept', $other))->assertSessionHasErrors();
        $this->actingAs(User::factory()->create())->post(route('offers.accept', $chosen))->assertForbidden();
        $this->assertSame(1, Contract::count());
    }

    public function test_the_owner_edits_the_staged_fields_before_choosing(): void
    {
        $request = $this->postProject();
        $this->actingAs($this->client)->get(route('requests.edit', $request))->assertOk()->assertSee('value="تطوير متجر إلكتروني"', false);
        $this->actingAs($this->client)->post(route('requests.update', $request), $this->payload(['project_title' => 'متجر إلكتروني للملابس', 'kind' => null]))
            ->assertSessionHasNoErrors();
        $this->assertSame(['متجر إلكتروني للملابس', ServiceRequest::KIND_STAGED], [$request->fresh()->project_title, $request->fresh()->kind]);
    }
}
