<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Tests\TestCase;

class OldProjectsRemovedTest extends TestCase
{
    use RefreshDatabase;

    public function test_old_project_urls_redirect_permanently_to_the_new_pages(): void
    {
        $this->get('projects')->assertStatus(301)->assertRedirect('/client-requests');
        $this->get('project/15-landing-page')->assertStatus(301)->assertRedirect('/client-requests');
        $this->get('my/project/index')->assertStatus(301)->assertRedirect('/my/requests');
        $this->get('my/project/update/3')->assertStatus(301)->assertRedirect('/my/requests');

        $this->actingAs(User::factory()->admin()->create())->get('admin/projects')->assertStatus(301)->assertRedirect('/admin/requests');
    }

    public function test_old_tables_are_gone(): void
    {
        foreach (['projects', 'project_offers', 'project_files'] as $table) {
            $this->assertFalse(Schema::hasTable($table), "$table should be dropped");
        }
        $this->assertTrue(Schema::hasTable('user_projects'), 'the portfolio table must stay');
    }

    public function test_old_project_notifications_are_removed(): void
    {
        $user = User::factory()->create();
        foreach (['App\Notifications\OfferAccepted', 'App\Notifications\NewMessage'] as $type) {
            DB::table('notifications')->insert([
                'id' => (string) Str::uuid(), 'type' => $type, 'notifiable_type' => User::class,
                'notifiable_id' => $user->id, 'data' => '{}', 'created_at' => now(), 'updated_at' => now(),
            ]);
        }

        (require database_path('migrations/2026_10_17_100000_drop_old_projects_system.php'))->up();

        $this->assertSame(['App\Notifications\NewMessage'], DB::table('notifications')->pluck('type')->all());
    }

    public function test_menus_link_to_the_new_system(): void
    {
        $this->actingAs(User::factory()->create())->get('/')
            ->assertOk()->assertSee(route('requests.create'), false)->assertSee(route('requests.index'), false)->assertDontSee(url('my/project/index'), false);
        $this->actingAs(User::factory()->freelancer()->create())->get('/')
            ->assertOk()->assertSee(route('freelancer.requests.index'), false)->assertSee(route('freelancer.offers.index'), false);
    }
}
