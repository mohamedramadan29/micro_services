<?php

namespace Tests\Feature;

use App\Http\Middleware\EnsureUserIsAdmin;
use App\Models\User;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class AdminMiddlewareTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Route::middleware(['web', 'auth', 'admin'])->get('/_test/admin-only', fn () => 'ok');
    }

    private function user(string $type, int $status = 1): User
    {
        $user = new User();
        $user->forceFill(['id' => 1, 'account_type' => $type, 'status' => $status]);

        return $user;
    }

    public function test_alias_is_registered(): void
    {
        $this->assertSame(EnsureUserIsAdmin::class, app('router')->getMiddleware()['admin']);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/_test/admin-only')->assertRedirect();
    }

    public function test_regular_user_is_forbidden(): void
    {
        $this->actingAs($this->user('user'))->get('/_test/admin-only')->assertForbidden();
    }

    public function test_inactive_admin_is_forbidden(): void
    {
        $this->actingAs($this->user('admin', 0))->get('/_test/admin-only')->assertForbidden();
    }

    public function test_active_admin_is_allowed(): void
    {
        $this->actingAs($this->user('admin'))->get('/_test/admin-only')->assertOk()->assertSee('ok');
    }
}
