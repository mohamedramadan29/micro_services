<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/** Group D (account lists) on the shared account frame `website/user/layouts/account` (2026-10-31). */
class AccountListPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_account_list_pages_use_the_account_frame(): void
    {
        $freelancer = User::factory()->freelancer()->create();
        foreach (['my/specializations', 'orders', 'purches', 'my/products/purches', 'tickets', 'ticket/create', 'chats'] as $url) {
            $this->actingAs($freelancer)->get($url)->assertOk()
                ->assertSee('rf-acc-card', false)->assertSee('dashboard-navbar', false)->assertDontSee('_list_jobs_wraps', false);
        }
    }

    public function test_tickets_show_their_reply_state_and_thread_to_their_owner_only(): void
    {
        $owner = User::factory()->create();
        $id = DB::table('tickets')->insertGetId(['user_id' => $owner->id, 'title' => 'مشكلة في الدفع', 'content' => 'نص', 're-send' => 1, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('ticket_messages')->insert(['user_id' => $owner->id, 'ticket_id' => $id, 'content' => 'لم يظهر الطلب', 'sender_type' => 'user', 'created_at' => now(), 'updated_at' => now()]);

        $this->actingAs($owner)->get('tickets')->assertSee('مشكلة في الدفع')->assertSee('وصل رد الفريق');
        $this->actingAs($owner)->get('ticket/' . $id)->assertOk()->assertSee('لم يظهر الطلب')->assertSee('rf-tk-thread', false);
        $this->actingAs(User::factory()->create())->get('ticket/' . $id)->assertDontSee('لم يظهر الطلب');
    }
}
