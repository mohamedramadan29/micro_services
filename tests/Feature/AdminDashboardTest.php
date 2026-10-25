<?php

namespace Tests\Feature;

use App\Models\HelpRequest;
use App\Models\OnlinePayment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** The admin home page reports and the organised sidebar (2026-10-18). */
class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_dashboard_shows_period_reports_and_what_waits_for_the_team(): void
    {
        \App\Models\admin\Setting::create(['website_commission' => '10', 'website_balance' => 0]);
        $admin = User::factory()->admin()->create();
        $client = User::factory()->create(['name' => 'عميل التقرير']);
        User::factory()->pendingFreelancer()->create();
        HelpRequest::create(['user_id' => $client->id, 'whatsapp' => '+201012345678', 'message' => 'مساعدة', 'status' => HelpRequest::STATUS_NEW]);
        OnlinePayment::create(['user_id' => $client->id, 'purpose' => OnlinePayment::PURPOSE_BALANCE, 'description' => 'شحن رصيد', 'amount' => 250, 'paid_amount' => 250,
            'status' => OnlinePayment::STATUS_PAID, 'paid_at' => now(), 'return_token' => 'x']);

        foreach (['today', '7', '30', '90', 'bogus'] as $period) {
            $this->actingAs($admin)->get(route('admin.dashboard', ['period' => $period]))->assertOk();
        }

        $this->actingAs($admin)->get(route('admin.dashboard', ['period' => '7']))
            ->assertSee('يحتاج تدخّل الفريق الآن')
            ->assertSee('طلبات مساعدة جديدة')
            ->assertDontSee('مستقلون جاهزون للمراجعة والاعتماد') // just registered: still filling in his profile
            ->assertSee('عميل التقرير')
            ->assertSee('شحن رصيد')
            ->assertSee(\App\Support\Money::format(250));
    }

    public function test_the_sidebar_starts_with_users_and_marks_the_open_page(): void
    {
        $admin = User::factory()->admin()->create();
        User::factory()->pendingFreelancer()->create();

        $html = $this->actingAs($admin)->get(route('admin.help-requests.index'))->assertOk()->getContent();
        $this->assertStringContainsString('data-server-active', $html);
        // Users is the first group, before the requests group.
        $this->assertLessThan(strpos($html, 'طلبات الخدمات</span>'), strpos($html, 'المستخدمون</span>'));
        // The open page is marked and its group is open.
        $this->assertMatchesRegularExpression('/slide-item active rf-an-current" href="[^"]*admin\/help-requests"/', $html);
        $this->assertSame(1, substr_count($html, 'rf-an-current'));

        // The most specific match wins: site-test settings, not the site-tests list.
        $html = $this->actingAs($admin)->get(route('admin.site-tests.settings'))->getContent();
        $this->assertMatchesRegularExpression('/rf-an-current" href="[^"]*site-tests\/settings"/', $html);
        $this->assertSame(1, substr_count($html, 'rf-an-current'));
    }

    public function test_the_users_page_uses_todays_words_filters_and_exports(): void
    {
        $admin = User::factory()->admin()->create();
        User::factory()->create(['name' => 'عميلة البحث', 'email' => 'find-me@example.com']);
        User::factory()->freelancer()->create(['name' => 'مستقل معتمد']);
        $blocked = User::factory()->create(['name' => 'محظور قديم', 'block_status' => 0]);

        $page = $this->actingAs($admin)->get('admin/users')->assertOk();
        $page->assertSee('عميل')->assertSee('مستقل')->assertSee('العملاء')->assertSee('المستقلون');
        $page->assertDontSee(User::TYPE_CLIENT)->assertDontSee(User::TYPE_FREELANCER); // never the stored words

        $this->actingAs($admin)->get('admin/users?tab=freelancers')->assertSee('مستقل معتمد')->assertDontSee('عميلة البحث');
        $this->actingAs($admin)->get('admin/users?q=find-me')->assertSee('عميلة البحث')->assertDontSee('مستقل معتمد');
        $this->actingAs($admin)->get('admin/users?state=blocked')->assertSee('محظور قديم')->assertDontSee('عميلة البحث');

        $csv = $this->actingAs($admin)->get('admin/users?tab=clients&export=csv')->assertOk()->streamedContent();
        $this->assertStringContainsString('عميلة البحث', $csv);
        $this->assertStringNotContainsString('مستقل معتمد', $csv);

        // Block / unblock, but never yourself.
        $this->actingAs($admin)->post('admin/block_status/' . $blocked->id);
        $this->assertSame(1, (int) $blocked->fresh()->block_status);
        $this->actingAs($admin)->post('admin/block_status/' . $admin->id);
        $this->assertSame(1, (int) $admin->fresh()->block_status);
    }

    public function test_a_blocked_or_deactivated_account_is_signed_out_on_its_next_request(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->get(route('dashboard'))->assertOk();

        $user->forceFill(['block_status' => 0])->save();
        $this->get(route('dashboard'))->assertRedirect(route('user_login'))->assertSessionHasErrors();
        $this->assertGuest();

        // An admin blocked while in the panel goes back to the admin login.
        $admin = User::factory()->admin()->create(['block_status' => 0]);
        $this->actingAs($admin)->get('admin/dashboard')->assertRedirect(url('admin'));
        $this->assertGuest();

        // Deactivated, and background (JSON) requests get a 401 instead of a page.
        $this->actingAs(User::factory()->inactive()->create())->getJson(route('dashboard'))->assertStatus(401);
        $this->assertGuest();
    }

    public function test_a_session_of_a_deleted_account_is_ended_and_pages_still_open(): void
    {
        // A real browser session that still holds the id of an account deleted since.
        $user = User::factory()->create();
        $sessionKey = \Illuminate\Support\Facades\Auth::guard()->getName();
        $user->delete();

        $this->withSession([$sessionKey => $user->id])->withCookie('analytics_consent', '1')->get('/')->assertOk();
        $this->assertGuest();
        $this->assertFalse(session()->has($sessionKey));
    }
}
