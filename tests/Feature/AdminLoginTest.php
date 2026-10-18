<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminLoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_active_admin_can_log_in(): void
    {
        $admin = User::factory()->admin()->create();

        $this->post('admin/admin_login', ['email' => $admin->email, 'password' => 'password'])
            ->assertRedirect('admin/dashboard');

        $this->assertAuthenticatedAs($admin);
    }

    public function test_regular_user_is_rejected_and_logged_out(): void
    {
        $user = User::factory()->create();

        $this->post('admin/admin_login', ['email' => $user->email, 'password' => 'password'])
            ->assertSessionHas('Error_Message');

        $this->assertGuest();
    }

    public function test_inactive_admin_is_rejected_and_logged_out(): void
    {
        $admin = User::factory()->admin()->inactive()->create();

        $this->post('admin/admin_login', ['email' => $admin->email, 'password' => 'password'])
            ->assertSessionHas('Error_Message');

        $this->assertGuest();
    }

    public function test_wrong_password_is_rejected(): void
    {
        $admin = User::factory()->admin()->create();

        $this->post('admin/admin_login', ['email' => $admin->email, 'password' => 'wrong'])
            ->assertSessionHas('Error_Message');

        $this->assertGuest();
    }

    public function test_regular_user_cannot_open_admin_pages(): void
    {
        $this->actingAs(User::factory()->create())
            ->get('admin/users')
            ->assertForbidden();
    }
}
