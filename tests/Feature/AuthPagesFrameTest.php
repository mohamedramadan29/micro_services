<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Group C (sign-in pages) on the new design: one sheet + the four promises beside it (2026-10-31). */
class AuthPagesFrameTest extends TestCase
{
    use RefreshDatabase;

    public function test_sign_in_pages_share_the_sheet_and_the_promises(): void
    {
        foreach (['login', 'register', 'forget-password'] as $url) {
            $this->get($url)->assertOk()
                ->assertSee('nf-auth-grid', false)->assertSee('nf-auth-aside', false)
                ->assertSee('المبلغ يبقى لدى نفّذها');
        }
        $this->get('forget-password')->assertSee('العودة لتسجيل الدخول');

        $user = User::factory()->create(['account_type' => null]);
        $this->actingAs($user)->get('account-type')->assertOk()->assertSee('nf-auth-grid', false)->assertSee('أكمل حسابك');
    }
}
