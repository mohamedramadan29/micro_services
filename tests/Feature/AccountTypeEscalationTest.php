<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccountTypeEscalationTest extends TestCase
{
    use RefreshDatabase;

    private function payload(string $accountType): array
    {
        return [
            'name' => 'Test User',
            'account_type' => $accountType,
            'email' => 'user@example.com',
            'info' => 'about me',
            'phone' => '0100000000',
        ];
    }

    public function test_user_cannot_promote_self_to_admin(): void
    {
        $user = User::factory()->create(['account_type' => 'مشتري']);

        $this->actingAs($user)
            ->post('update-account', $this->payload('admin'))
            ->assertSessionHasErrors('account_type');

        $this->assertSame('مشتري', $user->fresh()->account_type);
        $this->actingAs($user->fresh())->get('admin/users')->assertForbidden();
    }

    public function test_user_can_pick_an_allowed_account_type(): void
    {
        $user = User::factory()->create(['account_type' => 'مشتري']);

        $this->actingAs($user)
            ->post('update-account', $this->payload('بائع'))
            ->assertSessionHasNoErrors();

        $this->assertSame('بائع', $user->fresh()->account_type);
    }
}
