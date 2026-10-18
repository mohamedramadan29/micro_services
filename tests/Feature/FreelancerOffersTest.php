<?php

namespace Tests\Feature;

use App\Models\admin\Category;
use App\Models\admin\SubCategory;
use App\Models\OfferMessage;
use App\Models\RequestOffer;
use App\Models\ServiceRequest;
use App\Models\User;
use App\Notifications\NewOfferNotification;
use App\Notifications\OfferMessageNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FreelancerOffersTest extends TestCase
{
    use RefreshDatabase;

    private SubCategory $ads;
    private SubCategory $design;
    private User $client;
    private User $freelancer;
    private ServiceRequest $open;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('private_uploads');

        $category = Category::create(['name' => 'تسويق', 'slug' => 'marketing', 'image' => 'x.png', 'status' => 1]);
        $this->ads = SubCategory::create(['name' => 'إعلانات المواقع', 'slug' => 'ads', 'image' => 'x.png', 'parent_id' => $category->id, 'status' => 1]);
        $this->design = SubCategory::create(['name' => 'تصميم هوية', 'slug' => 'identity', 'image' => 'x.png', 'parent_id' => $category->id, 'status' => 1]);

        $this->client = User::factory()->create(['name' => 'سارة العميلة']);
        $this->freelancer = User::factory()->freelancer()->create(['name' => 'أحمد المستقل']);
        $this->freelancer->specializations()->attach($this->ads->id);

        $this->open = $this->makeRequest($this->ads, ['budget_min' => 300, 'budget_max' => 600, 'problem' => 'زيادة مبيعات متجر إلكتروني']);
        $this->open->answers()->create(['label' => 'المنتج', 'type' => 'short_text', 'value' => 'كريمات العناية بالبشرة', 'sort_order' => 1]);
    }

    private function makeRequest(SubCategory $service, array $attributes = []): ServiceRequest
    {
        $request = ServiceRequest::create($attributes + [
            'user_id' => $this->client->id,
            'category_id' => $service->parent_id,
            'sub_category_id' => $service->id,
            'category_name' => 'تسويق',
            'service_name' => $service->name,
            'path' => ServiceRequest::PATH_EXECUTE,
            'status' => ServiceRequest::STATUS_OPEN,
            'problem' => 'وصف المشروع',
            'contact_name' => 'سارة العميلة',
            'contact_phone' => '0100-555-7788',
            'contact_email' => 'sara.client@example.com',
            'published_at' => now(),
        ]);
        $request->update(['number' => 'REQ-' . (1000 + $request->id)]);

        return $request;
    }

    private function offerPayload(array $overrides = []): array
    {
        return $overrides + [
            'price' => 450,
            'duration_days' => 7,
            'scope' => 'إعداد حملة إعلانية كاملة مع التقارير الأسبوعية',
            'message' => 'لدي خبرة خمس سنوات في حملات المتاجر الإلكترونية',
            'portfolio_links' => ['https://portfolio.example.com/ads'],
        ];
    }

    private function sendOffer(?User $freelancer = null, array $overrides = [])
    {
        return $this->actingAs($freelancer ?? $this->freelancer)
            ->post(route('freelancer.offers.save', $this->open), $this->offerPayload($overrides));
    }

    public function test_board_shows_only_open_requests_in_the_freelancers_services(): void
    {
        $otherService = $this->makeRequest($this->design, ['problem' => 'شعار جديد لمطعم']);
        $inquiry = $this->makeRequest($this->ads, ['status' => ServiceRequest::STATUS_INQUIRY, 'path' => ServiceRequest::PATH_EXPLORE, 'published_at' => null, 'problem' => 'استفسار سعر خاص']);

        $this->actingAs($this->freelancer)->get(route('freelancer.requests.index'))
            ->assertOk()
            ->assertSee($this->open->number)
            ->assertDontSee($otherService->number)
            ->assertDontSee($inquiry->number)
            ->assertDontSee('sara.client@example.com');

        $this->actingAs($this->client)->get(route('freelancer.requests.index'))->assertForbidden();
        $this->actingAs(User::factory()->freelancer()->create())->get(route('freelancer.requests.index'))->assertOk()->assertSee('لم تختر تخصصاتك بعد');
    }

    public function test_board_filters_and_sorting(): void
    {
        $cheap = $this->makeRequest($this->ads, ['budget_min' => 50, 'budget_max' => 100, 'problem' => 'حملة صغيرة']);
        $old = $this->makeRequest($this->ads, ['published_at' => now()->subDays(20), 'problem' => 'طلب قديم']);
        $cheap->files()->create(['original_name' => 'a.pdf', 'path' => 'x/a.pdf', 'size' => 10]);
        $board = fn (array $query) => $this->actingAs($this->freelancer)->get(route('freelancer.requests.index', $query));

        $board(['budget_from' => 200])->assertSee($this->open->number)->assertDontSee($cheap->number);
        $board(['budget_to' => 150])->assertSee($cheap->number)->assertDontSee($this->open->number);
        $board(['published' => 'week'])->assertSee($this->open->number)->assertDontSee($old->number);
        $board(['q' => 'كريمات'])->assertSee($this->open->number)->assertDontSee($cheap->number);
        $board(['files' => 'yes'])->assertSee($cheap->number)->assertDontSee($this->open->number);
        $board(['sort' => 'budget_high'])->assertSeeInOrder([$this->open->number, $cheap->number]);
        $board(['service' => $this->design->id])->assertSee('لا توجد طلبات مطابقة');

        // "Best" puts requests with fewer offers first.
        $this->open->offers()->create(['freelancer_id' => User::factory()->freelancer()->create()->id, 'price' => 100, 'duration_days' => 3, 'scope' => 'x', 'message' => 'y']);
        $board(['sort' => 'best'])->assertSeeInOrder([$cheap->number, $this->open->number]);
    }

    public function test_freelancer_sees_the_request_without_client_contact_details(): void
    {
        $this->actingAs($this->freelancer)->get(route('freelancer.requests.show', $this->open))
            ->assertOk()
            ->assertSee('كريمات العناية بالبشرة')
            ->assertSee('زيادة مبيعات متجر إلكتروني')
            ->assertDontSee('sara.client@example.com')
            ->assertDontSee('0100-555-7788')
            ->assertDontSee('سارة العميلة');

        $outside = $this->makeRequest($this->design);
        $inquiry = $this->makeRequest($this->ads, ['status' => ServiceRequest::STATUS_INQUIRY, 'published_at' => null]);
        $this->actingAs($this->freelancer)->get(route('freelancer.requests.show', $outside))->assertForbidden();
        $this->actingAs($this->freelancer)->get(route('freelancer.requests.show', $inquiry))->assertForbidden();
        $this->actingAs($this->client)->get(route('freelancer.requests.show', $this->open))->assertForbidden();
    }

    public function test_freelancer_can_download_files_only_of_visible_requests(): void
    {
        Storage::disk('private_uploads')->put('service-requests/1/brief.pdf', 'pdf');
        $file = $this->open->files()->create(['original_name' => 'brief.pdf', 'path' => 'service-requests/1/brief.pdf', 'size' => 3]);

        $this->actingAs($this->freelancer)->get(route('freelancer.requests.file', [$this->open, $file]))->assertOk()->assertDownload('brief.pdf');
        $this->actingAs(User::factory()->freelancer()->create())->get(route('freelancer.requests.file', [$this->open, $file]))->assertForbidden();
    }

    public function test_sending_an_offer_notifies_the_client_and_editing_keeps_one_offer(): void
    {
        Notification::fake();

        $this->sendOffer(null, ['price' => 0])->assertSessionHasErrors('price');
        $this->sendOffer(null, ['scope' => 'قصير'])->assertSessionHasErrors('scope');
        $this->sendOffer(null, ['portfolio_files' => [UploadedFile::fake()->create('x.exe', 10)]])->assertSessionHasErrors('portfolio_files.0');

        $this->sendOffer(null, ['portfolio_files' => [UploadedFile::fake()->create('work.pdf', 50, 'application/pdf')]])->assertSessionHasNoErrors();
        $offer = RequestOffer::firstOrFail();
        $this->assertSame(RequestOffer::STATUS_NEW, $offer->status);
        $this->assertSame(1, $offer->files()->count());
        Storage::disk('private_uploads')->assertExists($offer->files()->first()->path);
        Notification::assertSentTo($this->client, NewOfferNotification::class);

        $this->sendOffer(null, ['price' => 400])->assertSessionHasNoErrors();
        $this->assertSame(1, RequestOffer::count());
        $this->assertEquals(400, $offer->fresh()->price);
    }

    public function test_offers_need_an_open_request_in_the_freelancers_services(): void
    {
        $this->open->update(['status' => ServiceRequest::STATUS_INQUIRY]);
        $this->sendOffer()->assertForbidden();

        $outsider = User::factory()->freelancer()->create();
        $this->open->update(['status' => ServiceRequest::STATUS_OPEN]);
        $this->sendOffer($outsider)->assertForbidden();
        $this->actingAs($this->client)->post(route('freelancer.offers.save', $this->open), $this->offerPayload())->assertForbidden();

        $this->assertSame(0, RequestOffer::count());
    }

    public function test_withdrawn_offer_is_hidden_from_the_client_and_can_be_resent(): void
    {
        $this->sendOffer();
        $offer = RequestOffer::firstOrFail();

        $this->actingAs(User::factory()->freelancer()->create())->post(route('freelancer.offers.withdraw', $offer))->assertForbidden();
        $this->actingAs($this->freelancer)->post(route('freelancer.offers.withdraw', $offer));
        $this->assertSame(RequestOffer::STATUS_WITHDRAWN, $offer->fresh()->status);

        $this->actingAs($this->client)->get(route('requests.show', $this->open))->assertDontSee('أحمد المستقل');
        $this->actingAs($this->client)->get(route('offers.chat', $offer))->assertNotFound();

        $this->sendOffer();
        $this->assertSame(RequestOffer::STATUS_NEW, $offer->fresh()->status);
        $this->assertSame(1, RequestOffer::count());
    }

    public function test_client_compares_offers_with_tabs_sorting_and_saving(): void
    {
        $this->sendOffer(null, ['price' => 550, 'duration_days' => 10]);
        $second = User::factory()->freelancer()->create(['name' => 'منى المصممة']);
        $second->specializations()->attach($this->ads->id);
        $this->sendOffer($second, ['price' => 380, 'duration_days' => 5]);
        [$first, $cheaper] = [RequestOffer::where('freelancer_id', $this->freelancer->id)->first(), RequestOffer::where('freelancer_id', $second->id)->first()];

        $this->actingAs($this->client)->get(route('requests.show', [$this->open, 'tab' => 'new']))
            ->assertOk()->assertSee('أحمد المستقل')->assertSee('منى المصممة');
        $this->assertSame(RequestOffer::STATUS_VIEWED, $first->fresh()->status);

        $this->actingAs($this->client)->get(route('requests.show', [$this->open, 'sort' => 'price']))->assertSeeInOrder(['منى المصممة', 'أحمد المستقل']);
        $this->actingAs($this->client)->get(route('requests.show', [$this->open, 'sort' => 'newest']))->assertSeeInOrder(['منى المصممة', 'أحمد المستقل']);

        $this->actingAs($this->freelancer)->post(route('offers.save', $first))->assertForbidden();
        $this->actingAs($this->client)->post(route('offers.save', $first));
        $this->assertTrue($first->fresh()->is_saved);
        $this->actingAs($this->client)->get(route('requests.show', [$this->open, 'tab' => 'saved']))
            ->assertSee('أحمد المستقل')->assertDontSee('منى المصممة');

        // Freelancers never see each other's offers.
        $this->actingAs($second)->get(route('freelancer.requests.show', $this->open))->assertDontSee('أحمد المستقل');
    }

    public function test_offer_chat_between_the_two_parties(): void
    {
        Notification::fake();
        $this->sendOffer();
        $offer = RequestOffer::firstOrFail();

        $this->actingAs($this->client)->post(route('offers.messages.store', $offer), ['body' => 'هل يشمل العرض كتابة النص الإعلاني؟'])->assertRedirect();
        $this->assertSame(RequestOffer::STATUS_IN_DISCUSSION, $offer->fresh()->status);
        Notification::assertSentTo($this->freelancer, OfferMessageNotification::class);

        $this->actingAs($this->freelancer)->get(route('offers.chat', $offer))
            ->assertOk()->assertSee('هل يشمل العرض كتابة النص الإعلاني؟')->assertDontSee('sara.client@example.com');
        $this->assertNotNull(OfferMessage::first()->read_at);

        $this->actingAs($this->freelancer)->post(route('offers.messages.store', $offer), ['body' => 'نعم، يشمل نصين وتعديلًا واحدًا.']);
        Notification::assertSentTo($this->client, OfferMessageNotification::class);
        $this->assertSame(2, OfferMessage::where('service_request_id', $this->open->id)->where('request_offer_id', $offer->id)->count());

        $stranger = User::factory()->freelancer()->create();
        $this->actingAs($stranger)->get(route('offers.chat', $offer))->assertForbidden();
        $this->actingAs($stranger)->post(route('offers.messages.store', $offer), ['body' => 'مرحبا'])->assertForbidden();
    }

    public function test_offer_files_are_private_to_the_two_parties(): void
    {
        $this->sendOffer(null, ['portfolio_files' => [UploadedFile::fake()->create('work.pdf', 20, 'application/pdf')]]);
        $offer = RequestOffer::firstOrFail();
        $file = $offer->files()->first();

        $this->actingAs($this->client)->get(route('offers.file', [$offer, $file]))->assertOk();
        $this->actingAs($this->freelancer)->get(route('offers.file', [$offer, $file]))->assertOk();
        $this->actingAs(User::factory()->create())->get(route('offers.file', [$offer, $file]))->assertForbidden();
    }

    public function test_opening_a_notification_marks_it_read_and_stays_on_site(): void
    {
        $this->sendOffer();
        $notification = $this->client->notifications()->firstOrFail();
        $this->assertStringContainsString($this->open->number, $notification->data['text']);

        $this->actingAs($this->client)->get(route('notifications.open', $notification->id))
            ->assertRedirect(url('/my/requests/' . $this->open->number . '#offers'));
        $this->assertNotNull($notification->fresh()->read_at);

        $notification->update(['data' => ['text' => 'x', 'url' => 'https://evil.example.com'], 'read_at' => null]);
        $this->actingAs($this->client)->get(route('notifications.open', $notification->id))->assertRedirect(url('dashboard'));
        $this->actingAs($this->freelancer)->get(route('notifications.open', $notification->id))->assertNotFound();
    }
}
