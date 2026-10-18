<?php

namespace Tests\Feature;

use Anhskohbo\NoCaptcha\Facades\NoCaptcha;
use App\Models\admin\Category;
use App\Models\admin\SubCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class AccountTypeAndSpecializationTest extends TestCase
{
    use RefreshDatabase;

    private function registerPayload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Test User',
            'username' => 'testuser',
            'email' => 'new@example.com',
            'password' => 'secret123',
            'confirm-password' => 'secret123',
            'account_type' => User::TYPE_FREELANCER,
            'g-recaptcha-response' => 'token',
        ], $overrides);
    }

    private function subCategory(array $attributes = []): SubCategory
    {
        $category = Category::create(['name' => 'تسويق', 'slug' => 'marketing', 'image' => 'x.png', 'status' => 1]);

        return SubCategory::create(array_merge([
            'name' => 'إعلانات المواقع', 'slug' => 'site-ads', 'image' => 'x.png', 'parent_id' => $category->id, 'status' => 1,
        ], $attributes));
    }

    public function test_registration_saves_the_chosen_account_type(): void
    {
        NoCaptcha::shouldReceive('verifyResponse')->andReturn(true);
        Mail::fake();

        $this->post('register', $this->registerPayload())->assertSessionHasErrors('whatsapp');
        $this->post('register', $this->registerPayload(['whatsapp' => '12']))->assertSessionHasErrors('whatsapp');
        $this->post('register', $this->registerPayload(['whatsapp' => '+20 100 000 0000']))->assertSessionHasNoErrors();

        $user = User::where('email', 'new@example.com')->first();
        $this->assertSame([User::TYPE_FREELANCER, '+20 100 000 0000', User::FREELANCER_PENDING, null], [$user->account_type, $user->whatsapp, $user->freelancer_status, $user->whatsapp_verified_at]);
        $this->assertFalse($user->isApprovedFreelancer());
    }

    public function test_registration_requires_a_client_or_freelancer_type(): void
    {
        NoCaptcha::shouldReceive('verifyResponse')->andReturn(true);
        Mail::fake();

        $this->post('register', $this->registerPayload(['account_type' => 'admin']))->assertSessionHasErrors('account_type');
        $this->post('register', $this->registerPayload(['account_type' => '']))->assertSessionHasErrors('account_type');

        $this->assertDatabaseMissing('users', ['email' => 'new@example.com']);
    }

    public function test_login_returns_to_the_intended_page(): void
    {
        NoCaptcha::shouldReceive('verifyResponse')->andReturn(true);
        $user = User::factory()->freelancer()->create();

        $this->get('my/specializations')->assertRedirect();

        $this->post('login', ['email' => $user->email, 'password' => 'password', 'g-recaptcha-response' => 'token'])
            ->assertRedirect(url('my/specializations'));
    }

    public function test_user_without_type_must_choose_one_first(): void
    {
        $user = User::factory()->create(['account_type' => null]);

        $this->actingAs($user)->get('my/specializations')->assertRedirect(route('account.type'));

        $this->actingAs($user)->post('account-type', ['account_type' => 'admin'])->assertSessionHasErrors('account_type');
        $this->actingAs($user)->post('account-type', ['account_type' => User::TYPE_FREELANCER])->assertSessionHasErrors('whatsapp');
        $this->actingAs($user)->post('account-type', ['account_type' => User::TYPE_FREELANCER, 'whatsapp' => '+201000000000'])
            ->assertRedirect(route('specializations.edit'));

        $this->assertTrue($user->fresh()->isFreelancer());
        $this->assertSame(User::FREELANCER_PENDING, $user->fresh()->freelancer_status);
    }

    public function test_account_type_cannot_be_changed_once_chosen_through_that_page(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('account-type', ['account_type' => User::TYPE_FREELANCER]);

        $this->assertTrue($user->fresh()->isClient());
    }

    public function test_freelancer_can_save_specializations(): void
    {
        $subCategory = $this->subCategory();
        $user = User::factory()->freelancer()->create();

        $this->actingAs($user)->get('my/specializations')->assertOk()->assertSee('إعلانات المواقع');

        $this->actingAs($user)->post('my/specializations', ['specializations' => [$subCategory->id]])
            ->assertSessionHasNoErrors();

        $this->assertSame([$subCategory->id], $user->specializations()->pluck('sub_categories.id')->all());
    }

    public function test_specializations_reject_inactive_or_missing_services(): void
    {
        $inactive = $this->subCategory(['status' => 0]);
        $user = User::factory()->freelancer()->create();

        $this->actingAs($user)->post('my/specializations', ['specializations' => [$inactive->id]])
            ->assertSessionHasErrors('specializations.0');
        $this->actingAs($user)->post('my/specializations', ['specializations' => [999]])
            ->assertSessionHasErrors('specializations.0');
        $this->actingAs($user)->post('my/specializations', [])->assertSessionHasErrors('specializations');

        $this->assertSame(0, $user->specializations()->count());
    }

    public function test_clients_cannot_open_specializations(): void
    {
        $subCategory = $this->subCategory();
        $user = User::factory()->create();

        $this->actingAs($user)->get('my/specializations')->assertRedirect(url('dashboard'));
        $this->actingAs($user)->post('my/specializations', ['specializations' => [$subCategory->id]]);

        $this->assertSame(0, $user->specializations()->count());
    }

    public function test_admin_can_set_repeat_client_threshold(): void
    {
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin)->get('admin/public_settings')->assertOk();

        $payload = ['website_title' => 'نفذها', 'website_desc' => '', 'website_color' => '#3fb697'];

        $this->actingAs($admin)
            ->post('admin/public_settings/update', $payload + ['website_commission' => 10, 'repeat_client_threshold' => 3])
            ->assertSessionHasNoErrors();
        $this->assertDatabaseHas('settings', ['repeat_client_threshold' => 3]);

        $this->actingAs($admin)
            ->post('admin/public_settings/update', $payload + ['website_commission' => 150, 'repeat_client_threshold' => 1])
            ->assertSessionHasErrors(['website_commission', 'repeat_client_threshold']);
    }
}
