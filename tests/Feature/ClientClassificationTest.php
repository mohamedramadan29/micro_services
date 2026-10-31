<?php

namespace Tests\Feature;

use App\Models\admin\Category;
use App\Models\admin\Setting;
use App\Models\admin\SubCategory;
use App\Models\HelpRequest;
use App\Models\RequestAdminMessage;
use App\Models\ServiceRequest;
use App\Models\User;
use App\Notifications\RequestUpdateNotification;
use App\Services\ServiceRequests\ClientClassifier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class ClientClassificationTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private SubCategory $service;

    protected function setUp(): void
    {
        parent::setUp();
        Notification::fake();
        Setting::create(['website_commission' => '10', 'repeat_client_threshold' => 2]);
        $category = Category::create(['name' => 'تسويق', 'slug' => 'm', 'image' => 'x.png', 'status' => 1]);
        $this->service = SubCategory::create(['name' => 'إعلانات المواقع', 'slug' => 'ads', 'image' => 'x.png', 'parent_id' => $category->id, 'status' => 1]);
        $this->admin = User::factory()->admin()->create();
    }

    private function request(User $client, string $status = ServiceRequest::STATUS_OPEN): ServiceRequest
    {
        $request = ServiceRequest::create([
            'user_id' => $client->id, 'category_id' => $this->service->parent_id, 'sub_category_id' => $this->service->id,
            'service_name' => 'إعلانات المواقع', 'path' => 'execute', 'status' => $status,
            'problem' => 'حملة', 'contact_name' => $client->name, 'contact_phone' => '0100', 'contact_email' => $client->email, 'published_at' => now(),
        ]);
        $request->update(['number' => 'REQ-' . (1000 + $request->id)]);

        return $request;
    }

    private function tags(User $client): array
    {
        return app(ClientClassifier::class)->classify([$client->id])->get($client->id)['tags'] ?? [];
    }

    public function test_first_second_and_repeat_clients(): void
    {
        $client = User::factory()->create();
        $this->request($client);
        $this->assertSame(['first_time', 'never_completed'], $this->tags($client));

        $this->request($client);
        $this->assertSame(['repeat', 'second_time', 'never_completed'], $this->tags($client));

        Setting::first()->update(['repeat_client_threshold' => 3]);
        $this->assertSame(['second_time', 'never_completed'], $this->tags($client));

        $this->request($client);
        $this->assertSame(['repeat', 'never_completed'], $this->tags($client));
    }

    public function test_the_admin_decides_whether_repeat_replaces_second_time(): void
    {
        $client = User::factory()->create();
        $this->request($client);
        $this->request($client);

        Setting::first()->update(['client_tags_overlap' => false]);
        $this->assertSame(['repeat', 'never_completed'], $this->tags($client));

        // With a higher threshold the second contact is still «ثاني مرة».
        Setting::first()->update(['repeat_client_threshold' => 3]);
        $this->assertSame(['second_time', 'never_completed'], $this->tags($client));
    }

    public function test_help_requests_count_as_contacts_only_when_the_admin_chooses(): void
    {
        $client = User::factory()->create();
        $this->request($client);
        HelpRequest::create(['user_id' => $client->id, 'whatsapp' => '+201000000000', 'message' => 'أي خدمة تناسبني؟', 'number' => 'HLP-1001']);
        $helpOnly = User::factory()->create();
        HelpRequest::create(['user_id' => $helpOnly->id, 'whatsapp' => '+201000000001', 'message' => 'سؤال', 'number' => 'HLP-1002']);

        $this->assertSame(['first_time', 'never_completed'], $this->tags($client));
        $this->assertSame([], $this->tags($helpOnly));

        Setting::first()->update(['client_count_mode' => 'requests_and_help']);
        $this->assertSame(['repeat', 'second_time', 'never_completed'], $this->tags($client));
        $this->assertSame(['first_time', 'never_completed'], $this->tags($helpOnly));
        $row = app(ClientClassifier::class)->classify([$client->id])->get($client->id);
        $this->assertSame([1, 2], [$row['requests'], $row['contacts']]);

        // The admin list follows (the cache is cleared by the setting and by new help requests).
        $this->actingAs($this->admin)->get(route('admin.clients.index'))->assertOk()->assertSee($helpOnly->name);
    }

    public function test_the_classification_rules_are_saved_from_the_site_settings(): void
    {
        $this->actingAs($this->admin)->get('admin/public_settings')->assertOk()->assertSee('ما يُحسب كتواصل للعميل');
        $this->post('admin/public_settings/update', [
            'website_title' => 'نفذها', 'website_desc' => 'd', 'website_color' => '#3FB699', 'website_commission' => 10,
            'repeat_client_threshold' => 2, 'client_count_mode' => 'requests_and_help', 'client_tags_overlap' => '0',
        ])->assertSessionHasNoErrors();
        $this->assertSame(['requests_and_help', false], [Setting::first()->client_count_mode, (bool) Setting::first()->client_tags_overlap]);

        $this->post('admin/public_settings/update', ['website_commission' => 10, 'repeat_client_threshold' => 2, 'client_count_mode' => 'everything'])
            ->assertSessionHasErrors('client_count_mode');
    }

    public function test_completed_work_switches_never_completed_to_completed_before(): void
    {
        $client = User::factory()->create();
        $request = $this->request($client, ServiceRequest::STATUS_IN_PROGRESS);
        $this->assertContains('never_completed', $this->tags($client));

        $request->update(['status' => ServiceRequest::STATUS_COMPLETED]);
        $this->assertContains('completed_before', $this->tags($client));
        $this->assertNotContains('never_completed', $this->tags($client));
    }

    public function test_asking_and_answering_follow_who_spoke_last(): void
    {
        $client = User::factory()->create();
        $request = $this->request($client);

        $this->actingAs($client)->post(route('requests.messages.store', $request), ['body' => 'كم تكلفة الحملة لشهرين؟'])->assertSessionHas('Success_message');
        $this->assertContains('asking', $this->tags($client));
        Notification::assertSentTo($this->admin, RequestUpdateNotification::class);

        $this->actingAs($this->admin)->post(route('admin.requests.messages.store', $request), ['body' => 'هل تفضل فيسبوك أم إنستغرام؟'])->assertSessionHas('success');
        $tags = $this->tags($client);
        $this->assertContains('answering', $tags);
        $this->assertNotContains('asking', $tags);
        Notification::assertSentTo($client, RequestUpdateNotification::class);
    }

    public function test_several_classifications_can_combine(): void
    {
        $client = User::factory()->create();
        $this->request($client, ServiceRequest::STATUS_COMPLETED);
        $current = $this->request($client);
        RequestAdminMessage::create(['service_request_id' => $current->id, 'sender_id' => $this->admin->id, 'from_admin' => true, 'body' => 'سؤال']);

        $this->assertSame(['repeat', 'second_time', 'completed_before', 'answering'], $this->tags($client));
    }

    public function test_admin_client_list_with_categories_in_order_and_counts(): void
    {
        $first = User::factory()->create(['name' => 'عميل جديد']);
        $this->request($first);
        $returning = User::factory()->create(['name' => 'عميل عائد']);
        $this->request($returning, ServiceRequest::STATUS_COMPLETED);
        $this->request($returning);
        User::factory()->freelancer()->create(['name' => 'مستقل بلا طلبات']);

        $this->actingAs(User::factory()->create())->get(route('admin.clients.index'))->assertForbidden();

        $this->actingAs($this->admin)->get(route('admin.clients.index'))
            ->assertOk()
            ->assertSeeInOrder(array_column(ClientClassifier::CATEGORIES, 'label'))
            ->assertSee('عميل جديد')->assertSee('عميل عائد')->assertDontSee('مستقل بلا طلبات');

        $this->actingAs($this->admin)->get(route('admin.clients.index', ['category' => 'first_time']))
            ->assertSee('عميل جديد')->assertDontSee('عميل عائد');
        $this->actingAs($this->admin)->get(route('admin.clients.index', ['category' => 'completed_before']))
            ->assertSee('عميل عائد')->assertDontSee('عميل جديد');
        $this->actingAs($this->admin)->get(route('admin.clients.index', ['q' => $returning->email]))
            ->assertSee('عميل عائد')->assertDontSee('عميل جديد');
    }

    public function test_client_profile_shows_the_full_record(): void
    {
        $client = User::factory()->create(['name' => 'سارة']);
        $request = $this->request($client);
        RequestAdminMessage::create(['service_request_id' => $request->id, 'sender_id' => $client->id, 'from_admin' => false, 'body' => 'هل يمكن البدء الأسبوع القادم؟']);

        $this->actingAs($this->admin)->get(route('admin.clients.show', $client))
            ->assertOk()->assertSee($request->number)->assertSee('عميل عم يسأل')->assertSee('بانتظار ردنا');
    }

    public function test_a_new_client_without_requests_has_a_profile_and_is_listed(): void
    {
        // Bug 2026-10-31: admin/clients/{id} was a 404 for a client who had just registered.
        $new = User::factory()->create(['name' => 'عميل سجّل اليوم', 'phone' => '+201011112222']);
        \App\Models\Suggestion::create(['submission_key' => (string) \Illuminate\Support\Str::uuid(), 'reference_code' => 'SUG-1001', 'user_id' => $new->id,
            'user_role' => 'client', 'title' => 'اقتراح العميل الجديد', 'description' => 'شرح', 'status' => 'new']);
        $withRequest = User::factory()->create(['name' => 'عميل له طلب']);
        $this->request($withRequest);

        $this->actingAs($this->admin)->get(route('admin.clients.show', $new))->assertOk()
            ->assertSee('عميل سجّل اليوم')->assertSee('مسجّل بلا طلبات بعد')->assertSee('لم يرسل أي طلب خدمة بعد')
            ->assertSee('+201011112222')->assertSee('نشط')->assertSee('اقتراح العميل الجديد');

        // «كل العملاء» lists him (by registration date) and the new section counts only him.
        $this->actingAs($this->admin)->get(route('admin.clients.index'))->assertOk()->assertSee('عميل سجّل اليوم')->assertSee('عميل له طلب');
        $this->actingAs($this->admin)->get(route('admin.clients.index', ['category' => 'no_requests']))->assertOk()
            ->assertSee('عميل سجّل اليوم')->assertDontSee('عميل له طلب');
        $this->actingAs($this->admin)->get(route('admin.clients.index', ['category' => 'first_time']))->assertDontSee('عميل سجّل اليوم');
        $this->actingAs($this->admin)->get(route('admin.clients.index', ['q' => 'سجّل اليوم']))->assertSee('عميل سجّل اليوم');

        // A freelancer goes to his own page; an admin account has no client profile.
        $freelancer = User::factory()->freelancer()->create();
        $this->actingAs($this->admin)->get(route('admin.clients.show', $freelancer))->assertRedirect(route('admin.freelancers.show', $freelancer));
        $this->actingAs($this->admin)->get(route('admin.clients.show', User::factory()->admin()->create()))->assertNotFound();
        // A search sent as an array never breaks the page (here or on any other search page).
        foreach ([route('admin.clients.index'), route('admin.help-requests.index'), route('admin.freelancers.index'), route('admin.site-tests.index'),
                  route('admin.contracts.index'), route('admin.catalog.services'), route('freelancers.index')] as $page) {
            $this->actingAs($this->admin)->get($page . '?q[]=x')->assertOk();
        }
    }

    public function test_team_messages_are_private_to_the_client_and_the_team(): void
    {
        $client = User::factory()->create();
        $request = $this->request($client);
        $freelancer = User::factory()->freelancer()->create();
        $freelancer->specializations()->attach($this->service->id);

        $this->actingAs($freelancer)->post(route('requests.messages.store', $request), ['body' => 'مرحبا'])->assertForbidden();
        $this->actingAs(User::factory()->create())->post(route('admin.requests.messages.store', $request), ['body' => 'x'])->assertForbidden();

        $this->actingAs($this->admin)->post(route('admin.requests.messages.store', $request), ['body' => 'رسالة خاصة من الفريق']);
        $this->actingAs($client)->get(route('requests.show', $request))->assertSee('رسالة خاصة من الفريق');
        $this->assertNotNull(RequestAdminMessage::first()->read_at);
        $this->actingAs($freelancer)->get(route('freelancer.requests.show', $request))->assertDontSee('رسالة خاصة من الفريق');
    }

    public function test_cached_classifications_refresh_when_records_change(): void
    {
        $first = User::factory()->create(['name' => 'هاني']);
        $this->request($first);
        $this->actingAs($this->admin)->get(route('admin.clients.index'))->assertSee('هاني');

        $second = User::factory()->create(['name' => 'ليلى']);
        $request = $this->request($second);
        $this->actingAs($this->admin)->get(route('admin.clients.index'))->assertSee('ليلى');

        RequestAdminMessage::create(['service_request_id' => $request->id, 'sender_id' => $second->id, 'from_admin' => false, 'body' => 'سؤال']);
        $this->actingAs($this->admin)->get(route('admin.clients.index', ['category' => 'asking']))->assertSee('ليلى')->assertDontSee('هاني');
    }
}
