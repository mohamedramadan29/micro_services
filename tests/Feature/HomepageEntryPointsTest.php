<?php

namespace Tests\Feature;

use App\Models\AnalyticsEvent;
use App\Models\ContentBlock;
use App\Models\ContentRevision;
use App\Models\HelpRequest;
use App\Models\ServiceForm;
use App\Models\ServiceRequest;
use App\Models\User;
use App\Models\admin\SubCategory;
use App\Services\Analytics\Tracker;
use App\Services\Content\ContentManager;
use Database\Seeders\ServicesCatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/** Client file «المربعات الخمسة» (2026-10-16): five homepage entry points, their pages, admin and tracking. */
class HomepageEntryPointsTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        Notification::fake();
        Storage::fake('private_uploads');
        $this->admin = User::factory()->admin()->create();
        $this->seed(ServicesCatalogSeeder::class);
    }

    private function service(string $name = 'تصميم الشعارات'): SubCategory
    {
        return SubCategory::where('name', $name)->firstOrFail();
    }

    private function project(array $overrides = []): array
    {
        return $overrides + [
            'title' => 'متجر ملابس أونلاين',
            'description' => str_repeat('أحتاج متجرًا إلكترونيًا لبيع الملابس مع الدفع. ', 2),
            'sub_category_id' => $this->service()->id,
            'budget_min' => 3000,
            'budget_max' => 6000,
            'deadline' => now()->addMonth()->format('Y-m-d'),
        ];
    }

    public function test_a_visitor_writes_a_project_logs_in_and_publishes_it_once(): void
    {
        $this->get(route('projects.new'))->assertOk()->assertSee('اكتب مشروعك وانشره مباشرة')->assertSee('احفظ واستمر بتسجيل الدخول');

        // A visitor: the text becomes a draft and he is sent to log in.
        $this->post(route('projects.store'), $this->project())->assertRedirect(route('user_login'));
        $this->assertSame(0, ServiceRequest::count());

        $client = User::factory()->create(['phone' => null]);
        $this->actingAs($client)->get(route('projects.new'))->assertOk()
            ->assertSee('استعدنا مشروعك')->assertSee('متجر ملابس أونلاين', false)->assertSee('رقم التواصل');

        $this->actingAs($client)->post(route('projects.store'), $this->project())->assertSessionHasErrors('contact_phone');
        $payload = $this->project(['contact_phone' => '+201011112222', 'attachments' => [UploadedFile::fake()->create('brief.pdf', 40, 'application/pdf')]]);
        $response = $this->actingAs($client)->withCookie(Tracker::CONSENT_COOKIE, '1')->post(route('projects.store'), $payload);
        $project = ServiceRequest::sole();
        $response->assertRedirect(route('requests.show', $project));

        $this->assertSame([ServiceRequest::SOURCE_DIRECT, ServiceRequest::STATUS_OPEN, 'متجر ملابس أونلاين', 'تصميم الشعارات', 3000.0, 6000.0],
            [$project->source, $project->status, $project->project_title, $project->service_name, (float) $project->budget_min, (float) $project->budget_max]);
        $this->assertNotNull($project->published_at);
        $this->assertSame('+201011112222', $client->fresh()->phone);
        $this->assertCount(1, $project->files);
        $this->assertSame(['الميزانية', 'الموعد المطلوب'], $project->answers->pluck('label')->all());
        $this->assertTrue(AnalyticsEvent::where('type', 'project_published')->exists());

        // A double click publishes nothing more.
        $this->actingAs($client)->post(route('projects.store'), $payload)->assertRedirect(route('requests.show', $project));
        $this->assertSame(1, ServiceRequest::count());

        // Freelancers of that service see it like any request, without contact data.
        $freelancer = User::factory()->freelancer()->create();
        $freelancer->specializations()->attach($this->service()->id);
        $this->actingAs($freelancer)->get(route('freelancer.requests.show', $project))->assertOk()
            ->assertSee('متجر ملابس أونلاين')->assertSee('الميزانية')->assertDontSee('+201011112222');
        $this->actingAs($freelancer)->get(route('projects.new'))->assertOk()->assertSee('نشر المشاريع متاح لحسابات العملاء');
        $this->actingAs($freelancer)->post(route('projects.store'), $payload)->assertForbidden();
    }

    public function test_the_owner_edits_a_direct_project_while_it_is_open(): void
    {
        $client = User::factory()->create(['phone' => '0100']);
        $this->actingAs($client)->post(route('projects.store'), $this->project());
        $project = ServiceRequest::sole();

        $this->actingAs($client)->get(route('requests.edit', $project))->assertRedirect(route('projects.edit', $project));
        $this->actingAs($client)->get(route('projects.edit', $project))->assertOk()->assertSee('تعديل المشروع ' . $project->number);
        $this->actingAs($client)->post(route('projects.update', $project), $this->project(['title' => 'متجر ملابس وأحذية', 'budget_max' => '']))
            ->assertRedirect(route('requests.show', $project));

        $project->refresh();
        $this->assertSame(['متجر ملابس وأحذية', null], [$project->project_title, $project->budget_max]);
        $this->assertNotNull($project->edited_at);
        $this->actingAs(User::factory()->create())->get(route('projects.edit', $project))->assertForbidden();
    }

    public function test_diagnosis_sends_a_site_owner_to_the_check_and_others_to_a_ready_request(): void
    {
        $service = $this->service();
        ServiceForm::create(['sub_category_id' => $service->id, 'status' => ServiceForm::STATUS_PUBLISHED]);

        $this->get(route('diagnose'))->assertOk()->assertSee('شخّص مشروعك مجانًا')->assertSee(route('site-tests.index'), false)->assertSee($service->category->name);
        $this->get(route('site-tests.index', ['url' => 'https://example.com']))->assertOk()->assertSee('value="https://example.com"', false);

        $this->post(route('diagnose.store'), [])->assertSessionHasErrors('group');
        $this->withCookie(Tracker::CONSENT_COOKIE, '1')->post(route('diagnose.store'), ['group' => $service->parent_id, 'service' => $service->id, 'description' => 'شعار لمطعم جديد', 'urgency' => 'now'])
            ->assertRedirect(route('diagnose.result'));
        $this->assertTrue(AnalyticsEvent::where('type', 'diagnosis_completed')->exists());

        $this->get(route('diagnose.result'))->assertOk()->assertSee('ملخص احتياجك')->assertSee('تصميم الشعارات')->assertSee('شعار لمطعم جديد')
            ->assertSee(route('requests.create', ['service' => $service->id]) . '&amp;from=diagnose', false)
            ->assertSee(route('projects.new', ['service' => $service->id]), false);

        $client = User::factory()->create();
        $this->actingAs($client)->get(route('requests.create', ['service' => $service->id, 'from' => 'diagnose']))->assertOk()->assertSee('شعار لمطعم جديد');
        $this->actingAs($client)->get(route('projects.new', ['service' => $service->id]))->assertOk()->assertSee('شعار لمطعم جديد');

        // Not sure: the team helps.
        $this->post(route('diagnose.store'), ['group' => $service->parent_id]);
        $this->get(route('diagnose.result'))->assertSee('لم تحدد خدمة بعينها')->assertSee(route('services.index', ['help' => 1]), false);
    }

    public function test_join_as_freelancer_uses_the_existing_account_and_tracks_completion(): void
    {
        $this->get(route('freelancers.join'))->assertOk()->assertSee('أنشئ حسابي كمستقل')->assertSee(url('register') . '?type=freelancer', false);
        $this->get(url('register') . '?type=freelancer')->assertOk()->assertSee('id="type_freelancer"', false);

        $freelancer = User::factory()->pendingFreelancer()->create(['whatsapp' => null, 'bio' => null]);
        $this->actingAs($freelancer)->get(route('freelancers.join'))->assertOk()->assertSee('أكمل الآن')->assertSee('بانتظار المراجعة');

        // Every step the freelancer controls, including his own professional photo (2026-10-20).
        $freelancer->forceFill(['email_verified_at' => now(), 'whatsapp' => '+201000000000', 'bio' => 'مصمم', 'availability' => array_key_first(User::AVAILABILITY), 'professional_photo' => 'test-photo'])->save();
        $freelancer->specializations()->attach($this->service()->id);
        \App\Models\front\UserPortfolio::forceCreate(['user_id' => $freelancer->id, 'title' => 'شعار', 'slug' => 'logo', 'description' => 'شعار', 'image' => 'logo.png', 'category_id' => $this->service()->parent_id]);
        $this->actingAs($freelancer)->withCookie(Tracker::CONSENT_COOKIE, '1')->get(route('freelancers.join'))->assertOk();
        $this->assertNotNull($freelancer->fresh()->freelancer_profile_completed_at);
        $this->assertSame(1, AnalyticsEvent::where('type', 'freelancer_profile_completed')->count());

        $this->actingAs(User::factory()->create())->get(route('freelancers.join'))->assertSee('حسابك الحالي مسجّل كعميل');
    }

    public function test_a_client_stuck_in_the_request_form_asks_for_help_with_its_context(): void
    {
        ServiceForm::create(['sub_category_id' => $this->service()->id, 'status' => ServiceForm::STATUS_PUBLISHED]);
        $client = User::factory()->create();
        $this->actingAs($client)->get(route('requests.create'))->assertOk()->assertSee('اتعثرت أو مش متأكد');

        $this->actingAs($client)->from(route('requests.create'))->withCookie(Tracker::CONSENT_COOKIE, '1')->post(route('services.help.store'), [
            'whatsapp' => '+201000000000', 'message' => 'مش عارف أختار', 'source' => 'request_form',
            'service' => $this->service()->id, 'context' => "الخدمة: تصميم الشعارات\nما كتبه: شعار",
        ])->assertRedirect(route('requests.create'))->assertSessionHas('Success_message');

        $help = HelpRequest::sole();
        $this->assertSame(['request_form', "الخدمة: تصميم الشعارات\nما كتبه: شعار"], [$help->source, $help->context]);
        $this->assertTrue(AnalyticsEvent::where('type', 'support_requested')->exists());
        $this->actingAs($this->admin)->get(route('admin.help-requests.show', $help))->assertOk()->assertSee('تعثّر في نموذج «اطلب عرضًا»')->assertSee('ما كتبه: شعار');
    }

    public function test_card_clicks_are_counted_and_the_admin_sees_results_per_card(): void
    {
        $content = app(ContentManager::class);
        $cards = $content->blocks('home.cards');
        $post = $cards->first(fn ($b) => $b->data['action'] === 'post_project');

        $this->withCookie(Tracker::CONSENT_COOKIE, '1')->get('/')->assertOk()->assertSee(route('home.card', $post->id), false);
        $this->withCookie(Tracker::CONSENT_COOKIE, '1')->get(route('home.card', $post->id))->assertRedirect(route('projects.new'));
        $this->get(route('home.card', 999999))->assertRedirect(url('/'));

        $this->actingAs($this->admin)->get(route('admin.home-cards.index'))->assertOk()
            ->assertSee('إدارة مربعات الصفحة الرئيسية')->assertSee('انشر مشروعك (النشر المباشر)')->assertSee('100%');
        $csv = $this->actingAs($this->admin)->get(route('admin.home-cards.index', ['export' => 'csv']))->assertOk()->assertHeader('Content-Type', 'text/csv; charset=UTF-8')->getContent();
        $this->assertStringContainsString('انشر مشروعك', $csv);
    }

    public function test_content_roles_history_and_rollback(): void
    {
        $content = app(ContentManager::class);
        $card = $content->blocks('home.cards')->first();
        $input = fn (array $o = []) => $o + ['title' => 'اطلب عرضًا الآن', 'description' => 'وصف', 'button_text' => 'ابدأ', 'action' => 'request', 'group' => 'primary', 'audience' => 'all', 'icon' => 'bi-star'];

        // The manager publishes: a revision is kept.
        $this->actingAs($this->admin)->post(route('admin.content.update', $card), $input())->assertSessionHas('success');
        $this->actingAs($this->admin)->post(route('admin.content.publish', 'home'))->assertSessionHas('success');
        $first = ContentRevision::sole();

        // A content editor changes texts only, as a draft, and cannot publish.
        $editor = User::factory()->admin()->create(['admin_role' => 'content']);
        $this->actingAs($editor)->post(route('admin.content.update', $card), $input(['title' => 'نص من المحرر', 'action' => 'staged']))->assertSessionHas('success');
        $this->assertSame(['نص من المحرر', 'request'], [$card->fresh()->draft['title'], $card->fresh()->draft['action']]);
        $this->actingAs($editor)->post(route('admin.content.publish', 'home'))->assertForbidden();
        $this->actingAs($editor)->post(route('admin.content.store', 'home.cards'))->assertForbidden();
        $this->actingAs($editor)->post(route('admin.site-tests.settings.save'), [])->assertForbidden();

        // An analyst only looks.
        $analyst = User::factory()->admin()->create(['admin_role' => 'analyst']);
        $this->actingAs($analyst)->get(route('admin.content.index'))->assertOk()->assertSee('دورك: اطلاع فقط');
        $this->actingAs($analyst)->get(route('admin.home-cards.index'))->assertOk();
        $this->actingAs($analyst)->post(route('admin.content.update', $card), $input())->assertForbidden();

        // A second publish, then roll back to the first one.
        $this->actingAs($this->admin)->post(route('admin.content.publish', 'home'));
        $this->assertSame('نص من المحرر', $card->fresh()->data['title']);
        $this->actingAs($this->admin)->post(route('admin.content.rollback', $first))->assertSessionHas('success');
        $this->assertSame('اطلب عرضًا الآن', $card->fresh()->data['title']);
        $this->assertSame(3, ContentRevision::count());
        $this->assertSame('rollback', ContentRevision::latest('id')->first()->action);
        auth()->logout();
        $this->get('/')->assertSee('اطلب عرضًا الآن');
    }
}
