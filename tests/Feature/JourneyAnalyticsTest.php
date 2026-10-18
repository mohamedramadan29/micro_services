<?php

namespace Tests\Feature;

use App\Models\admin\Package;
use App\Models\admin\Setting;
use App\Models\admin\SubCategory;
use App\Models\AnalyticsEvent;
use App\Models\AnalyticsVisit;
use App\Models\OnlinePayment;
use App\Models\User;
use App\Services\Analytics\Tracker;
use App\Services\Payments\PaymentGateway;
use Database\Seeders\ServicesCatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\Fakes\FakePaymentGateway;
use Tests\TestCase;

/** Journey analytics (client file «تعديلات», item 08): consent first, real events only. */
class JourneyAnalyticsTest extends TestCase
{
    use RefreshDatabase;

    private FakePaymentGateway $gateway;

    protected function setUp(): void
    {
        parent::setUp();
        Notification::fake();
        $this->gateway = new FakePaymentGateway();
        $this->app->instance(PaymentGateway::class, $this->gateway);
        Setting::create(['website_commission' => '10', 'website_balance' => 0]);
    }

    private function consented()
    {
        return $this->withCookie(Tracker::CONSENT_COOKIE, '1');
    }

    public function test_nothing_is_recorded_before_consent_and_declining_keeps_it_that_way(): void
    {
        $this->get('/')->assertOk()->assertSee('rf-consent')->assertSee('سياسة الخصوصية');
        $this->assertSame(0, AnalyticsVisit::count());

        $this->post(route('analytics.consent'), ['accept' => 0])->assertRedirect()->assertCookie(Tracker::CONSENT_COOKIE, '0');
        $this->withCookie(Tracker::CONSENT_COOKIE, '0')->get('/')->assertOk()->assertDontSee('rf-consent');
        $this->assertSame(0, AnalyticsVisit::count());

        $this->post(route('analytics.consent'), ['accept' => 1])->assertCookie(Tracker::CONSENT_COOKIE, '1');
        $this->consented()->get('/')->assertOk()->assertDontSee('class="rf-consent"', false);
        $this->assertSame(1, AnalyticsVisit::count());
    }

    public function test_sources_come_from_utm_ad_clicks_and_the_referrer(): void
    {
        $cases = [
            ['/?utm_source=facebook&utm_campaign=eid', [], 'facebook'],
            ['/?gclid=abc', [], 'google'],
            ['/', ['Referer' => 'https://www.youtube.com/watch?v=1'], 'youtube'],
            ['/blog', ['Referer' => 'https://www.google.com/'], 'blog'],
            ['/', ['Referer' => 'https://some-site.net/x'], 'referral'],
            ['/', [], 'direct'],
        ];
        foreach ($cases as [$url, $headers, $expected]) {
            $this->flushSession();
            $this->flushHeaders();
            $this->consented()->withHeaders($headers)->get($url)->assertOk();
            $this->assertSame($expected, AnalyticsVisit::latest('id')->first()->source, $url);
        }
        $this->assertSame('eid', AnalyticsVisit::where('source', 'facebook')->value('campaign'));

        // Bots are never counted.
        $this->flushSession();
        $this->consented()->withHeaders(['User-Agent' => 'Googlebot/2.1'])->get('/');
        $this->assertSame(count($cases), AnalyticsVisit::count());
    }

    public function test_the_entry_source_survives_the_consent_click(): void
    {
        $this->withHeaders(['Referer' => 'https://www.facebook.com/'])->get('/services')->assertOk();
        $this->assertSame(0, AnalyticsVisit::count());

        $this->post(route('analytics.consent'), ['accept' => 1]);
        $this->consented()->withHeaders(['Referer' => url('/services')])->get('/services')->assertOk();

        $visit = AnalyticsVisit::sole();
        $this->assertSame(['facebook', 'facebook.com', '/services'], [$visit->source, $visit->referrer_host, $visit->landing_path]);
    }

    public function test_a_request_journey_goes_from_service_view_to_submitted(): void
    {
        $this->seed(ServicesCatalogSeeder::class);
        $service = SubCategory::firstOrFail();
        $client = User::factory()->create();

        $this->consented()->get(route('site-tests.index'))->assertSee('data-track-start="site_test"', false);
        $this->flushSession();
        AnalyticsVisit::query()->delete();

        $this->consented()->actingAs($client)->get(route('requests.create', ['service' => $service->id]))->assertOk();
        $this->consented()->post(route('analytics.form-start'), ['service' => $service->id, 'form' => 'request'])->assertNoContent();
        $this->consented()->post(route('analytics.form-start'), ['service' => $service->id, 'form' => 'request'])->assertNoContent();

        $visit = AnalyticsVisit::sole();
        $this->assertSame(['service_view', 'form_start'], $visit->events->pluck('type')->all());
        $this->assertSame([$service->id, $service->id], $visit->events->pluck('sub_category_id')->all());
        $this->assertSame(['form_start', $client->id], [$visit->last_step, $visit->user_id]);
    }

    public function test_a_payment_is_tied_to_its_visit_and_completes_the_funnel_from_the_webhook(): void
    {
        $buyer = User::factory()->create();
        $package = Package::create(['name' => 'الباقة الذهبية', 'slug' => 'gold', 'description' => 'd', 'price' => 999]);

        $this->consented()->get('/?utm_source=instagram')->assertOk();
        $this->consented()->actingAs($buyer)->post(route('subscribe.plan', $package->id))->assertRedirect();
        $payment = OnlinePayment::sole();
        $visit = AnalyticsVisit::sole();
        $this->assertSame($visit->id, $payment->visit_id);
        $this->assertSame('payment_start', $visit->fresh()->last_step);

        // Confirmed later without a browser: the step still lands on the right visit.
        $this->gateway->markPaid((string) $payment->id);
        $this->artisan('payments:sync');
        $this->assertSame('order_complete', $visit->fresh()->last_step);
        $this->assertEquals(999, AnalyticsEvent::where('type', 'order_complete')->sole()->meta['amount']);

        // Without consent a payment has no visit and nothing is recorded.
        $this->flushSession();
        $this->withCookie(Tracker::CONSENT_COOKIE, '0')->actingAs($buyer)->post(route('subscribe.plan', $package->id));
        $this->assertNull(OnlinePayment::latest('id')->first()->visit_id);
        $this->assertSame(1, AnalyticsVisit::count());
    }

    public function test_the_abandon_reason_is_asked_after_a_failed_payment_and_only_its_visitor_can_answer(): void
    {
        $buyer = User::factory()->create();
        $package = Package::create(['name' => 'الباقة الذهبية', 'slug' => 'gold', 'description' => 'd', 'price' => 999]);
        $this->consented()->actingAs($buyer)->post(route('subscribe.plan', $package->id));
        $payment = OnlinePayment::sole();
        $visit = AnalyticsVisit::sole();

        $this->gateway->markFailed((string) $payment->id);
        $this->consented()->get(route('payments.return', ['onlinePayment' => $payment->id, 'token' => $payment->return_token]))
            ->assertSessionHas('abandon_payment', $payment->id);
        $this->consented()->get('/')->assertSee('لماذا لم تكمل الدفع');

        // Someone else (another visitor cookie) cannot answer for this payment.
        $this->withCookie(Tracker::VISITOR_COOKIE, '00000000-0000-0000-0000-000000000000')
            ->post(route('analytics.abandon-reason'), ['payment' => $payment->id, 'reason' => 'price']);
        $this->assertFalse(AnalyticsEvent::where('type', 'abandon_reason')->exists());

        $this->withCookie(Tracker::VISITOR_COOKIE, $visit->visitor_id)
            ->post(route('analytics.abandon-reason'), ['payment' => $payment->id, 'reason' => 'price', 'note' => 'غالي شوية'])->assertRedirect();
        $this->withCookie(Tracker::VISITOR_COOKIE, $visit->visitor_id)
            ->post(route('analytics.abandon-reason'), ['payment' => $payment->id, 'reason' => 'later']);
        $this->assertSame(['price'], AnalyticsEvent::where('type', 'abandon_reason')->get()->pluck('meta.reason')->all());
        $this->assertSame('payment_start', $visit->fresh()->last_step);
    }

    public function test_the_dashboard_is_admin_only_and_shows_funnel_sources_and_unknown_reasons(): void
    {
        $this->seed(ServicesCatalogSeeder::class);
        $service = SubCategory::firstOrFail();
        $make = function (string $source, array $steps) use ($service) {
            $visit = AnalyticsVisit::create(['visitor_id' => fake()->uuid(), 'source' => $source, 'landing_path' => '/', 'started_at' => now(), 'last_seen_at' => now()]);
            foreach ($steps as $step) {
                Tracker::recordOn($visit, $step, ['sub_category_id' => $service->id]);
            }

            return $visit;
        };
        $make('google', ['service_view', 'form_start', 'payment_start', 'order_complete']);
        $abandoned = $make('facebook', ['service_view', 'form_start', 'payment_start']);
        $make('facebook', ['service_view', 'payment_start']);
        Tracker::recordOn($abandoned, 'abandon_reason', ['meta' => ['payment' => 1, 'reason' => 'payment_method']]);
        $make('direct', ['page_view']);
        AnalyticsVisit::create(['visitor_id' => fake()->uuid(), 'source' => 'google', 'landing_path' => '/', 'started_at' => now()->subDays(60), 'last_seen_at' => now()->subDays(60)]);

        $this->actingAs(User::factory()->create())->get(route('admin.analytics.index'))->assertForbidden();

        $admin = User::factory()->admin()->create();
        $this->actingAs($admin)->get(route('admin.analytics.index'))->assertOk()
            ->assertViewHas('total', 4)->assertViewHas('completed', 1)->assertViewHas('abandoned', 2)
            ->assertViewHas('funnel', fn ($funnel) => array_column($funnel, 'count') === [3, 2, 0, 3, 1])
            ->assertSee('طريقة الدفع غير مناسبة')->assertSee('السبب غير معروف')
            ->assertSee('وافقوا على الإحصائيات');

        $this->get(route('admin.analytics.index', ['source' => 'facebook', 'status' => 'abandoned']))->assertViewHas('total', 2);
        $this->get(route('admin.analytics.index', ['status' => 'browsing']))->assertViewHas('total', 1);
        $this->get(route('admin.analytics.index', ['from' => now()->subDays(90)->format('Y-m-d')]))->assertViewHas('total', 5);
        $this->get(route('admin.analytics.index', ['service' => $service->id + 1]))->assertViewHas('total', 0);

        $this->get(route('admin.analytics.show', $abandoned))->assertOk()
            ->assertSee('بدء الدفع')->assertSee('طريقة الدفع غير مناسبة')->assertSee($service->name);
    }

    public function test_old_visits_are_pruned(): void
    {
        $old = AnalyticsVisit::create(['visitor_id' => 'a', 'source' => 'direct', 'landing_path' => '/', 'started_at' => now()->subMonths(14), 'last_seen_at' => now()->subMonths(14)]);
        Tracker::recordOn($old, 'page_view');
        AnalyticsVisit::create(['visitor_id' => 'b', 'source' => 'direct', 'landing_path' => '/', 'started_at' => now()->subMonths(2), 'last_seen_at' => now()]);

        $this->artisan('analytics:prune')->expectsOutputToContain('Deleted 1')->assertSuccessful();
        $this->assertSame(['b'], AnalyticsVisit::pluck('visitor_id')->all());
        $this->assertSame(0, AnalyticsEvent::count());
    }
}
