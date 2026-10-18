<?php

namespace Tests\Feature;

use App\Models\Contract;
use App\Models\ContractStage;
use App\Models\HelpRequest;
use App\Models\User;
use App\Models\admin\Blog;
use App\Services\Contracts\FreelancerChangeService;
use App\Services\Landing\LandingBuilder;
use Database\Seeders\ServicesCatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use RuntimeException;
use Tests\TestCase;

/** Bugs found by the second audit of the six client files (2026-10-17). */
class AuditRoundTwoFixesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Notification::fake();
    }

    public function test_limited_roles_cannot_change_data_through_links_or_publish_landing_pages(): void
    {
        $analyst = User::factory()->admin()->create(['admin_role' => 'analyst']);
        $editor = User::factory()->admin()->create(['admin_role' => 'content']);

        // A legacy page that deletes on a plain GET link.
        $this->actingAs($analyst)->post('admin/nafizha-portfolio/delete/1')->assertForbidden();
        $this->actingAs($editor)->post('admin/nafizha-portfolio/delete/1')->assertForbidden();
        $this->actingAs($analyst)->get('admin/email/campaigns/1/send')->assertStatus(405); // no longer a link that sends

        $page = app(LandingBuilder::class)->create(['title' => 'صفحة', 'slug' => 'test-page'], false);
        $this->actingAs($editor)->post(route('admin.landing.publish', $page))->assertForbidden();
        $this->actingAs($editor)->post(route('admin.landing.destroy', $page))->assertForbidden();
        $this->assertNotNull($page->fresh());
    }

    public function test_help_from_the_request_form_keeps_its_errors_apart_and_needs_a_country_code(): void
    {
        $this->seed(ServicesCatalogSeeder::class);
        $client = User::factory()->create();

        $this->actingAs($client)->from(route('requests.create'))->post(route('services.help.store'), [
            'whatsapp' => '01012345678', 'message' => 'مش عارف أكمل', 'source' => 'request_form',
        ])->assertRedirect(route('requests.create'))->assertSessionHasErrorsIn('stuck', 'whatsapp');
        $this->assertFalse(session('errors')->getBag('default')->any()); // the request form is not marked as failed

        // «00…» is accepted as the international form.
        $this->actingAs($client)->post(route('services.help.store'), ['whatsapp' => '00201012345678', 'message' => 'أحتاج مساعدة'])->assertSessionHasNoErrors();
        $this->assertSame('+201012345678', HelpRequest::sole()->whatsapp);
        $this->assertSame('https://wa.me/201012345678', HelpRequest::sole()->whatsappUrl());
    }

    public function test_an_old_lateness_that_was_settled_no_longer_lets_the_client_change_the_freelancer(): void
    {
        $client = User::factory()->create();
        $freelancer = User::factory()->freelancer()->create();
        $contract = Contract::create(['client_id' => $client->id, 'freelancer_id' => $freelancer->id, 'title' => 'متجر', 'status' => Contract::STATUS_ACTIVE]);
        $old = $contract->stages()->create(['sort_order' => 1, 'title' => 'أولى', 'deliverable' => 'x', 'duration_days' => 3, 'amount' => 100, 'revisions' => 2,
            'status' => ContractStage::STATUS_RELEASED, 'started_at' => now()->subDays(20), 'due_at' => now()->subDays(15), 'late_escalated_at' => now()->subDays(8)]);
        $current = $contract->stages()->create(['sort_order' => 2, 'title' => 'ثانية', 'deliverable' => 'x', 'duration_days' => 5, 'amount' => 100, 'revisions' => 2,
            'status' => ContractStage::STATUS_FUNDED, 'started_at' => now()->subDay(), 'due_at' => now()->addDays(4)]);

        try {
            app(FreelancerChangeService::class)->request($contract, $client, 'client', 'تأخر قديم');
            $this->fail('An old, settled lateness must not open the change request.');
        } catch (RuntimeException) {
        }

        // The running stage itself 7 days late: allowed.
        $current->update(['due_at' => now()->subDays(8), 'late_escalated_at' => now()]);
        app(FreelancerChangeService::class)->request($contract, $client, 'client', 'تأخر الآن');
        $this->assertSame(1, $contract->freelancerChanges()->count());
    }

    public function test_the_blog_archive_lists_drafts(): void
    {
        $admin = User::factory()->admin()->create();
        $category = \App\Models\admin\BlogCategory::forceCreate(['name' => 'قسم', 'slug' => 'qsm', 'status' => 1, 'image' => 'x.png']);
        Blog::create(['name' => 'مقال مسودة', 'slug' => 'draft-post', 'category_id' => $category->id, 'short_desc' => 'ملخص', 'desc' => '<p>نص</p>', 'image' => 'x.png',
            'publish_date' => now()->toDateString(), 'meta_title' => 'م', 'meta_desc' => 'و', 'meta_keywords' => 'ك', 'status' => 0, 'author' => $admin->id]);

        $this->actingAs($admin)->get('admin/blog/archived')->assertOk()->assertSee('مقال مسودة');
    }
}
