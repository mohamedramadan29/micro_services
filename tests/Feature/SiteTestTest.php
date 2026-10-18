<?php

namespace Tests\Feature;

use App\Models\admin\Category;
use App\Models\admin\SubCategory;
use App\Models\ServiceRequest;
use App\Models\SiteTest;
use App\Models\SiteTestRule;
use App\Models\User;
use App\Notifications\SiteTestNotification;
use App\Notifications\SiteTestReportReady;
use App\Services\SiteTests\PageSpeedEngine;
use App\Services\SiteTests\SiteTestEngine;
use App\Services\SiteTests\SiteTestSettings;
use App\Services\SiteTests\SiteUrlGuard;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\RateLimiter;
use RuntimeException;
use Tests\Fakes\FakeSiteTestEngine;
use Tests\TestCase;

/** Client file «اختبر موقعك مجاناً - الخدمة الثانية». */
class SiteTestTest extends TestCase
{
    use RefreshDatabase;

    private FakeSiteTestEngine $engine;
    private User $admin;
    private SubCategory $seo;

    protected function setUp(): void
    {
        parent::setUp();
        Notification::fake();
        RateLimiter::clear('site-test:127.0.0.1');
        $this->engine = new FakeSiteTestEngine();
        $this->app->instance(SiteTestEngine::class, $this->engine);
        // No real DNS in tests: example.com is public, internal.example resolves to a private address.
        $this->app->instance(SiteUrlGuard::class, new class extends SiteUrlGuard {
            protected function resolve(string $host): array
            {
                return ['example.com' => ['93.184.216.34'], 'internal.example' => ['10.0.0.5']][$host] ?? [];
            }
        });
        $this->admin = User::factory()->admin()->create();
        $category = Category::create(['name' => 'التسويق', 'slug' => 'm', 'status' => 1]);
        $this->seo = SubCategory::create(['name' => 'تحسين محركات البحث (SEO)', 'slug' => 'seo', 'parent_id' => $category->id, 'status' => 1]);
        SiteTestRule::create(['audit_code' => 'meta-description', 'sub_category_id' => $this->seo->id, 'priority' => 'high']);
    }

    private function check(array $input = []): SiteTest
    {
        $this->post(route('site-tests.store'), $input + ['url' => 'example.com/shop', 'categories' => ['performance', 'seo'], 'device' => 'mobile'])->assertRedirect();

        return SiteTest::latest('id')->firstOrFail();
    }

    private function resultPage(SiteTest $test)
    {
        return $this->get(route('site-tests.show', ['siteTest' => $test->number, 't' => $test->token]));
    }

    public function test_a_guest_checks_a_public_url_and_sees_the_summary_without_contact_data(): void
    {
        $this->get(route('site-tests.index'))->assertOk()->assertSee('اكتشف أهم فرص تحسين موقعك')->assertSee('فحص مجاني • دون تسجيل دخول');

        $test = $this->check();

        $this->assertSame(['SITE-1001', SiteTest::STATUS_DONE, 'https://example.com/shop', 'example.com'], [$test->number, $test->status, $test->url, $test->host]);
        $this->assertSame(['performance' => 70, 'seo' => 70], $test->scores['mobile']);
        $this->assertSame([['https://example.com/shop', 'mobile', ['performance', 'seo']]], $this->engine->calls);
        $this->assertNull($test->contact_phone);

        // The admin's rule makes the SEO issue high priority and suggests its service.
        $this->assertSame($this->seo->id, $test->suggested_sub_category_id);
        $this->resultPage($test)->assertOk()
            ->assertSeeInOrder(['الملخص الأولي', 'الأداء والسرعة', '70', 'المشكلات حسب الأولوية', 'عالية', 'لا يحتوي المستند على وصف تعريفي'])
            ->assertSee('Potential savings of 120 KiB')
            ->assertSee('شكراً لفحص موقعك.')
            ->assertSee('أظهر الفحص: لا يحتوي المستند على وصف تعريفي. إذا بتحب، منساعدك بخدمة تحسين محركات البحث (SEO).')
            ->assertSee('أرسل لي التقرير الكامل ومراجعة مختص');
    }

    public function test_private_invalid_and_credential_urls_never_start_a_check(): void
    {
        foreach (['http://localhost/admin', 'https://internal.example', 'https://user:pass@example.com', 'ftp://example.com', 'https://example.com:8080', 'http://10.0.0.1', 'not a url'] as $url) {
            $this->post(route('site-tests.store'), ['url' => $url, 'categories' => ['seo'], 'device' => 'mobile'])->assertSessionHasErrors('url');
        }
        $this->assertSame(0, SiteTest::count());
        $this->assertSame([], $this->engine->calls);
    }

    public function test_only_enabled_categories_and_devices_are_accepted(): void
    {
        SiteTestSettings::save(['categories' => ['seo'], 'devices' => ['mobile']]);

        $this->post(route('site-tests.store'), ['url' => 'example.com', 'categories' => ['performance'], 'device' => 'mobile'])->assertSessionHasErrors('url');
        $this->post(route('site-tests.store'), ['url' => 'example.com', 'categories' => ['seo'], 'device' => 'desktop'])->assertSessionHasErrors('url');
        $this->get(route('site-tests.index'))->assertDontSee('الأداء والسرعة')->assertDontSee('الكمبيوتر');
    }

    public function test_a_failed_check_shows_unavailable_never_a_fake_report(): void
    {
        $this->engine->fail = true;
        $test = $this->check();

        $this->assertSame(SiteTest::STATUS_FAILED, $test->status);
        $this->assertSame(0, $test->issues()->count());
        $this->resultPage($test)->assertOk()->assertSee('تعذّر الفحص')->assertDontSee('الملخص الأولي');
    }

    public function test_both_devices_keep_what_each_one_measured(): void
    {
        $this->engine->failDevices = ['desktop'];
        $test = $this->check(['device' => 'both', 'categories' => ['seo', 'mobile']]);

        $this->assertSame(SiteTest::STATUS_DONE, $test->status);
        $this->assertSame(['mobile'], array_keys($test->scores));
        $this->assertSame(2, $test->results()->count());
        $this->assertSame(1, $test->issues()->where('audit_code', 'meta-description')->count()); // one row per audit
    }

    public function test_results_are_private_to_their_link_or_session(): void
    {
        $test = $this->check();
        $this->resultPage($test)->assertOk();

        $this->flushSession();
        $this->get(route('site-tests.show', $test->number))->assertNotFound();
        $this->get(route('site-tests.show', ['siteTest' => $test->number, 't' => 'guess']))->assertNotFound();
        $this->get(route('site-tests.status', ['siteTest' => $test->number, 't' => $test->token]))->assertOk()->assertJson(['status' => 'done', 'finished' => true]);
    }

    public function test_the_hourly_limit_is_applied_per_visitor(): void
    {
        SiteTestSettings::save(['hourly_limit' => 2]);
        $this->check();
        $this->check();

        $this->post(route('site-tests.store'), ['url' => 'example.com', 'categories' => ['seo'], 'device' => 'mobile'])->assertSessionHasErrors('url');
        $this->assertSame(2, SiteTest::count());
    }

    public function test_full_report_request_asks_contact_data_and_alerts_the_team(): void
    {
        $test = $this->check();

        $this->post(route('site-tests.report', $test->number), ['t' => $test->token, 'contact_name' => 'أحمد'])->assertSessionHasErrors('contact_phone');
        $this->post(route('site-tests.report', $test->number), ['t' => $test->token, 'contact_name' => 'أحمد', 'contact_phone' => '+201000000000', 'goal' => 'زيادة المبيعات'])
            ->assertSessionHas('site_test_report');

        $test->refresh();
        $this->assertSame([SiteTest::REVIEW_REQUESTED, 'أحمد', null], [$test->review_status, $test->contact_name, $test->contact_email]);
        Notification::assertSentTo($this->admin, SiteTestNotification::class, fn ($n) => $n->toArray($this->admin)['tone'] === 'info');
        $this->resultPage($test)->assertSee('شكراً يا أحمد لفحص موقعك.');
    }

    public function test_no_is_saved_with_a_red_alert_and_creates_nothing(): void
    {
        $test = $this->check();

        $this->post(route('site-tests.choose', ['siteTest' => $test->number, 'choice' => 'no']), ['t' => $test->token])->assertSessionHas('site_test_declined');
        $this->post(route('site-tests.choose', ['siteTest' => $test->number, 'choice' => 'no']), ['t' => $test->token]);

        $this->assertSame('no', $test->fresh()->choice);
        $this->assertSame(0, ServiceRequest::count());
        Notification::assertSentToTimes($this->admin, SiteTestNotification::class, 1);
        Notification::assertSentTo($this->admin, SiteTestNotification::class, fn ($n) => $n->toArray($this->admin)['tone'] === 'danger' && $n->toArray($this->admin)['freelancers_url'] === null);
    }

    public function test_yes_asks_a_guest_to_log_in_then_publishes_exactly_one_request(): void
    {
        $test = $this->check();
        $this->post(route('site-tests.choose', ['siteTest' => $test->number, 'choice' => 'yes']), ['t' => $test->token])
            ->assertRedirect(route('site-tests.execute', ['siteTest' => $test->number, 't' => $test->token]));
        Notification::assertSentTo($this->admin, SiteTestNotification::class, fn ($n) => $n->toArray($this->admin)['tone'] === 'success');

        $this->get(route('site-tests.execute', ['siteTest' => $test->number, 't' => $test->token]))->assertRedirect(route('user_login'));

        // A client account without a phone: only the phone is asked for.
        $client = User::factory()->create(['phone' => null, 'name' => 'منى']);
        $this->actingAs($client)->get(route('site-tests.execute', ['siteTest' => $test->number, 't' => $test->token]))->assertOk()
            ->assertSee('نحتاج فقط البيانات الناقصة')->assertSee('name="contact_phone"', false)->assertDontSee('id="rf-st-contact_email"', false);

        $payload = ['t' => $test->token, 'contact_name' => 'منى', 'contact_phone' => '+201000000000', 'contact_email' => $client->email];
        $this->actingAs($client)->post(route('site-tests.publish', $test->number), $payload)->assertRedirect();
        $this->actingAs($client)->post(route('site-tests.publish', $test->number), $payload)->assertRedirect();

        $request = ServiceRequest::sole();
        $this->assertSame([ServiceRequest::STATUS_OPEN, $this->seo->id, $client->id, $test->id], [$request->status, $request->sub_category_id, $request->user_id, $request->site_test_id]);
        $this->assertStringContainsString('لا يحتوي المستند على وصف تعريفي', $request->problem);
        $this->assertStringNotContainsString('+201000000000', $request->problem);
        $this->assertSame($request->id, $test->fresh()->service_request_id);
        Notification::assertSentToTimes($this->admin, SiteTestNotification::class, 2); // yes + published
        Notification::assertSentTo($this->admin, SiteTestNotification::class, fn ($n) => $n->toArray($this->admin)['freelancers_url'] === '/client-requests/' . $request->number);

        // Matching freelancers see it on their board, without contact data.
        $freelancer = User::factory()->freelancer()->create();
        $freelancer->specializations()->attach($this->seo->id);
        $this->actingAs($freelancer)->get(route('freelancer.requests.show', $request))->assertOk()
            ->assertSee('لا يحتوي المستند على وصف تعريفي')->assertDontSee('+201000000000');

        // After publishing, the result page links to the request instead of the offer buttons.
        $this->actingAs($client)->get(route('site-tests.show', ['siteTest' => $test->number, 't' => $test->token]))->assertSee('نُشر طلبك ' . $request->number);
    }

    public function test_freelancer_accounts_cannot_turn_a_check_into_a_request(): void
    {
        $test = $this->check();
        $this->post(route('site-tests.choose', ['siteTest' => $test->number, 'choice' => 'yes']), ['t' => $test->token]);

        $freelancer = User::factory()->freelancer()->create();
        $this->actingAs($freelancer)->post(route('site-tests.publish', $test->number), ['t' => $test->token, 'contact_name' => 'x', 'contact_phone' => '+201000000000', 'contact_email' => 'x@example.com'])
            ->assertSessionHasErrors();
        $this->assertSame(0, ServiceRequest::count());
    }

    public function test_without_a_rule_there_is_no_offer_until_the_team_picks_a_service(): void
    {
        SiteTestRule::query()->delete();
        $test = $this->check();

        $this->assertNull($test->suggested_sub_category_id);
        $this->resultPage($test)->assertDontSee('منساعدك بخدمة');
        $this->post(route('site-tests.choose', ['siteTest' => $test->number, 'choice' => 'yes']), ['t' => $test->token])->assertSessionHasErrors();

        $this->actingAs($this->admin)->post(route('admin.site-tests.review', $test), ['suggested_sub_category_id' => $this->seo->id, 'review_status' => 'in_review'])->assertSessionHas('success');
        auth()->logout();
        $this->resultPage($test)->assertSee('منساعدك بخدمة تحسين محركات البحث (SEO)');
    }

    public function test_admin_reviews_adds_team_notes_and_sends_the_report(): void
    {
        $test = $this->check();
        $this->post(route('site-tests.report', $test->number), ['t' => $test->token, 'contact_name' => 'أحمد', 'contact_phone' => '+201000000000', 'contact_email' => 'ahmed@example.com']);

        $this->actingAs(User::factory()->create())->get(route('admin.site-tests.index'))->assertForbidden();
        $this->actingAs($this->admin)->get(route('admin.site-tests.index', ['filter' => 'report']))->assertOk()->assertSee($test->number)->assertSee('طلب التقرير');
        $this->actingAs($this->admin)->get(route('admin.site-tests.show', $test))->assertOk()->assertSee('meta-description')->assertSee('بدون قاعدة — للمراجعة');

        $this->actingAs($this->admin)->post(route('admin.site-tests.send', $test))->assertSessionHasErrors();
        $this->actingAs($this->admin)->post(route('admin.site-tests.issues.store', $test), ['title' => 'زر الإجراء الرئيسي غير بارز'])->assertSessionHas('success');
        $this->actingAs($this->admin)->post(route('admin.site-tests.review', $test), ['review_summary' => 'موقعك سريع لكن ينقصه وصف الصفحة.', 'review_status' => 'in_review'])->assertSessionHas('success');
        $this->actingAs($this->admin)->post(route('admin.site-tests.send', $test))->assertSessionHas('success');

        Notification::assertSentTo(new AnonymousNotifiable, SiteTestReportReady::class, fn ($n, $channels, $notifiable) => $notifiable->routes['mail'] === 'ahmed@example.com');
        auth()->logout();
        $this->resultPage($test)->assertSee('موقعك سريع لكن ينقصه وصف الصفحة.')->assertSee('مراجعة الفريق')->assertSee('زر الإجراء الرئيسي غير بارز');
    }

    public function test_admin_settings_and_rules(): void
    {
        $this->actingAs($this->admin)->get(route('admin.site-tests.settings'))->assertOk()->assertSee('ربط النتائج بالخدمات')->assertSee('meta-description');

        $this->actingAs($this->admin)->post(route('admin.site-tests.rules.save'), ['audit_code' => 'unused javascript', 'sub_category_id' => $this->seo->id, 'priority' => 'medium'])->assertSessionHasErrors('audit_code');
        $this->actingAs($this->admin)->post(route('admin.site-tests.rules.save'), ['audit_code' => 'unused-javascript', 'sub_category_id' => $this->seo->id, 'priority' => 'medium'])->assertSessionHas('success');
        $this->assertSame(2, SiteTestRule::count());

        $this->actingAs($this->admin)->post(route('admin.site-tests.settings.save'), [
            'categories' => ['seo'], 'devices' => ['desktop'], 'max_issues' => 1, 'hourly_limit' => 3, 'failure_message' => 'جرب لاحقًا',
        ])->assertSessionHas('success');
        $this->assertSame([false, ['seo'], 1], [SiteTestSettings::get('enabled'), SiteTestSettings::get('categories'), SiteTestSettings::get('max_issues')]);
        auth()->logout();
        $this->get(route('site-tests.index'))->assertNotFound();
    }

    public function test_a_recheck_keeps_the_old_result_and_links_them(): void
    {
        $first = $this->check();
        $this->post(route('site-tests.store'), ['url' => $first->url, 'categories' => $first->categories, 'device' => $first->device, 'recheck' => $first->number, 't' => $first->token]);

        $second = SiteTest::latest('id')->first();
        $this->assertNotSame($first->id, $second->id);
        $this->assertSame($first->id, $second->parent_id);
        $this->assertSame(SiteTest::STATUS_DONE, $first->fresh()->status);
    }

    public function test_pagespeed_engine_reads_scores_and_failed_audits(): void
    {
        config(['services.pagespeed.key' => 'psi-key']);
        Http::fake(['www.googleapis.com/*' => Http::response([
            'lighthouseResult' => [
                'categories' => [
                    'seo' => ['score' => 0.82, 'auditRefs' => [['id' => 'meta-description', 'weight' => 1], ['id' => 'viewport', 'weight' => 1]]],
                    'performance' => ['score' => null, 'auditRefs' => []],
                ],
                'audits' => [
                    'meta-description' => ['id' => 'meta-description', 'title' => 'لا يحتوي المستند على وصف', 'description' => 'اقرأ [المزيد](https://web.dev).', 'score' => 0, 'scoreDisplayMode' => 'binary'],
                    'viewport' => ['id' => 'viewport', 'title' => 'viewport', 'score' => 1, 'scoreDisplayMode' => 'binary'],
                    'font-size' => ['id' => 'font-size', 'title' => 'حجم الخط', 'score' => 0, 'scoreDisplayMode' => 'binary'],
                ],
            ],
        ])]);

        $result = (new PageSpeedEngine())->run('https://example.com/', 'mobile', ['seo', 'performance', 'mobile']);

        $this->assertSame(['seo' => 82, 'performance' => null, 'mobile' => 50], $result['scores']);
        $this->assertSame(['meta-description', 'font-size'], array_column($result['issues'], 'code'));
        $this->assertSame('اقرأ المزيد.', $result['issues'][0]['description']);
        Http::assertSent(fn ($request) => str_contains($request->url(), 'strategy=mobile') && str_contains($request->url(), 'locale=ar')
            && str_contains($request->url(), 'category=seo') && str_contains($request->url(), 'key=psi-key'));

        config(['services.pagespeed.key' => null]);
        $this->expectException(RuntimeException::class);
        (new PageSpeedEngine())->run('https://example.com/', 'mobile', ['seo']);
    }

    public function test_starting_rules_map_audits_to_catalog_services(): void
    {
        SiteTestRule::query()->delete();
        $this->seed(\Database\Seeders\ServicesCatalogSeeder::class);
        $this->seed(\Database\Seeders\SiteTestRulesSeeder::class);
        $this->seed(\Database\Seeders\SiteTestRulesSeeder::class);

        $this->assertSame('تحسين محركات البحث (SEO)', SiteTestRule::where('audit_code', 'meta-description')->first()->service->name);
        $this->assertSame('تحسين سرعة المواقع', SiteTestRule::where('audit_code', 'unused-javascript')->first()->service->name);
        $this->assertSame('برمجة وتصميم مواقع إلكترونية', SiteTestRule::where('audit_code', 'viewport')->first()->service->name);
        $this->assertSame(1, SiteTestRule::where('audit_code', 'viewport')->count());

        // A database seeded before the speed service existed: its speed rules move, an admin's own choice stays.
        $web = SubCategory::where('name', 'برمجة وتصميم مواقع إلكترونية')->first();
        $seo = SubCategory::where('name', 'تحسين محركات البحث (SEO)')->first();
        SiteTestRule::where('audit_code', 'speed-index')->update(['sub_category_id' => $web->id]);
        SiteTestRule::where('audit_code', 'offscreen-images')->update(['sub_category_id' => $seo->id]);
        $this->seed(\Database\Seeders\SiteTestRulesSeeder::class);
        $this->assertSame('تحسين سرعة المواقع', SiteTestRule::where('audit_code', 'speed-index')->first()->service->name);
        $this->assertSame('تحسين محركات البحث (SEO)', SiteTestRule::where('audit_code', 'offscreen-images')->first()->service->name);
    }

    public function test_admin_manages_the_report_questions_and_answers_reach_the_team(): void
    {
        $this->actingAs($this->admin)->post(route('admin.site-tests.questions.save'), ['questions' => [
            ['key' => 'goal', 'label' => 'هدف الموقع', 'type' => 'text', 'active' => 1],
            ['key' => 'market', 'label' => 'السوق المستهدف', 'type' => 'select', 'options' => "مصر\nالخليج", 'active' => 1, 'required' => 1],
            ['key' => '', 'label' => 'عدد الزيارات الشهرية', 'type' => 'textarea', 'active' => 1],
        ]])->assertSessionHas('success');
        $this->assertCount(3, \App\Services\SiteTests\SiteTestSettings::questions());
        $this->actingAs($this->admin)->post(route('admin.site-tests.questions.save'), ['questions' => [
            ['key' => 'x', 'label' => 'قائمة ناقصة', 'type' => 'select', 'options' => 'واحد', 'active' => 1],
        ]])->assertSessionHasErrors();
        auth()->logout();

        $test = $this->check();
        $this->resultPage($test)->assertSee('السوق المستهدف')->assertSee('عدد الزيارات الشهرية');
        $base = ['t' => $test->token, 'contact_name' => 'أحمد', 'contact_phone' => '+201000000000'];
        $this->post(route('site-tests.report', $test->number), $base + ['answers' => ['market' => 'أوروبا']])->assertSessionHasErrors('answers.market');
        $this->post(route('site-tests.report', $test->number), $base + ['answers' => ['market' => 'الخليج', 'goal' => 'مبيعات']])->assertSessionHas('site_test_report');

        $test->refresh();
        $this->assertSame(['هدف الموقع', 'السوق المستهدف'], array_column($test->answers, 'label'));
        $this->assertSame('مبيعات', $test->goal);
        $this->assertStringContainsString('السوق المستهدف: الخليج', app(\App\Services\SiteTests\SiteTestService::class)->problemText($test));
        $this->actingAs($this->admin)->get(route('admin.site-tests.show', $test))->assertSee('السوق المستهدف: <strong>الخليج</strong>', false);
    }
}
