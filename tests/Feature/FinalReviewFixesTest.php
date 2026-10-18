<?php

namespace Tests\Feature;

use App\Models\Contract;
use App\Models\ContractStage;
use App\Models\front\WithDraw;
use App\Models\OnlinePayment;
use App\Models\ServiceRequest;
use App\Models\User;
use App\Services\Contracts\ContractLedger;
use App\Services\ServiceRequests\RequestAdminService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use RuntimeException;
use Tests\TestCase;

/** Bugs found by the final code review of the whole project (2026-10-17). */
class FinalReviewFixesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Notification::fake();
        Mail::fake();
        $this->app->instance(\App\Services\Payments\PaymentGateway::class, new \Tests\Fakes\FakePaymentGateway());
        \App\Models\admin\Setting::create(['website_commission' => '10', 'website_balance' => 0]);
    }

    public function test_a_password_cannot_be_changed_without_the_emailed_token(): void
    {
        $admin = User::factory()->admin()->create(['email' => 'boss@example.com', 'password' => Hash::make('old-secret-1')]);

        // The old attack: post an email and a new password.
        $this->post('user/update_forget_password', ['email' => 'boss@example.com', 'password' => 'hacked123', 'confirm_password' => 'hacked123'])->assertRedirect(url('forget-password'));
        $this->assertTrue(Hash::check('old-secret-1', $admin->fresh()->password));

        // A guessed or forged code is refused, even with the right email.
        $forged = 'x|' . base64_encode('boss@example.com');
        $this->get('user/change-forget-password/' . $forged)->assertRedirect(url('forget-password'));
        $this->post('user/update_forget_password', ['code' => $forged, 'password' => 'hacked123', 'confirm_password' => 'hacked123']);
        $this->assertTrue(Hash::check('old-secret-1', $admin->fresh()->password));

        // The real flow: request → hashed token stored → the link works once, then is spent.
        $this->post('forget-password', ['email' => 'boss@example.com'])->assertRedirect();
        $row = DB::table('password_reset_tokens')->where('email', 'boss@example.com')->first();
        $this->assertNotNull($row);
        $this->assertNotSame('x', $row->token);
        // We cannot read the plain token from the hash; simulate the emailed one by storing a known token.
        DB::table('password_reset_tokens')->where('email', 'boss@example.com')->update(['token' => Hash::make('known-token'), 'created_at' => now()]);
        $code = 'known-token|' . base64_encode('boss@example.com');
        $this->get('user/change-forget-password/' . $code)->assertOk()->assertSee('name="code"', false);
        $this->post('user/update_forget_password', ['code' => $code, 'password' => 'new-secret-9', 'confirm_password' => 'new-secret-9'])->assertRedirect(url('login'));
        $this->assertTrue(Hash::check('new-secret-9', $admin->fresh()->password));
        $this->assertNull(DB::table('password_reset_tokens')->where('email', 'boss@example.com')->first());

        // An expired token is refused.
        DB::table('password_reset_tokens')->insert(['email' => 'boss@example.com', 'token' => Hash::make('late'), 'created_at' => now()->subHours(2)]);
        $this->post('user/update_forget_password', ['code' => 'late|' . base64_encode('boss@example.com'), 'password' => 'again-secret-9', 'confirm_password' => 'again-secret-9']);
        $this->assertTrue(Hash::check('new-secret-9', $admin->fresh()->password));
    }

    public function test_changing_the_email_needs_a_new_confirmation_and_admins_keep_their_type(): void
    {
        $freelancer = User::factory()->freelancer()->create();
        $this->assertNotNull($freelancer->email_verified_at);
        $response = $this->actingAs($freelancer)->post('update-account', ['name' => $freelancer->name, 'email' => 'other@example.com', 'phone' => '0100', 'info' => 'x', 'account_type' => User::TYPE_FREELANCER]);
        $response->assertSessionHasNoErrors();
        $this->assertNull($freelancer->fresh()->email_verified_at);
        $this->assertSame('other@example.com', $freelancer->fresh()->email);

        $admin = User::factory()->admin()->create();
        $this->actingAs($admin)->post('update-account', ['name' => 'x', 'email' => $admin->email, 'phone' => '0100', 'info' => 'x', 'account_type' => User::TYPE_CLIENT]);
        $this->assertSame(User::TYPE_ADMIN, $admin->fresh()->account_type);
    }

    public function test_a_rejected_or_deleted_withdrawal_returns_the_money_and_the_balance_never_goes_negative(): void
    {
        $user = User::factory()->freelancer()->create(['balance' => 300]);
        $bank = ['method' => 'bank', 'country' => 'مصر', 'account_name' => 'أحمد', 'account_number' => '123', 'bank_name' => 'بنك', 'swift_code' => 'SW', 'iban_code' => 'IB'];

        $this->actingAs($user)->post('withdraw_balance', $bank + ['amount' => 500])->assertSessionHasErrors();
        $this->actingAs($user)->post('withdraw_balance', $bank + ['amount' => 200])->assertSessionHasNoErrors();
        $this->assertEquals(100, $user->fresh()->balance);
        $this->actingAs($user)->post('withdraw_balance', $bank + ['amount' => 50])->assertSessionHasErrors(); // one pending at a time

        $withdraw = WithDraw::sole();
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin)->post('admin/withdraw/update/' . $withdraw->id, ['status' => 2]);
        $this->assertEquals(300, $user->fresh()->balance); // rejected → money back
        $this->actingAs($admin)->post('admin/withdraw/update/' . $withdraw->id, ['status' => 1]);
        $this->assertEquals(100, $user->fresh()->balance); // re-opened and paid → taken again
        $this->actingAs($admin)->post('admin/withdraw/update/' . $withdraw->id, ['status' => 'paid'])->assertSessionHasErrors();
    }

    public function test_a_request_with_a_live_contract_is_not_closed_from_the_request_page(): void
    {
        $client = User::factory()->create();
        $freelancer = User::factory()->freelancer()->create();
        $request = ServiceRequest::create(['user_id' => $client->id, 'service_name' => 'متجر', 'path' => 'execute', 'status' => ServiceRequest::STATUS_IN_PROGRESS, 'kind' => ServiceRequest::KIND_STAGED,
            'project_title' => 'متجر', 'problem' => 'x', 'contact_name' => 'x', 'contact_phone' => '0100', 'contact_email' => 'x@x.x', 'published_at' => now()]);
        $request->update(['number' => 'REQ-' . (1000 + $request->id)]);
        Contract::create(['service_request_id' => $request->id, 'client_id' => $client->id, 'freelancer_id' => $freelancer->id, 'title' => 'متجر', 'status' => Contract::STATUS_ACTIVE]);

        $this->expectException(RuntimeException::class);
        app(RequestAdminService::class)->close($request, 'not_feasible', 'x', User::factory()->admin()->create());
    }

    public function test_a_stuck_easykash_funding_can_be_cancelled_and_a_refund_ends_the_project_without_completing_it(): void
    {
        $client = User::factory()->create(['balance' => 1000]);
        $freelancer = User::factory()->freelancer()->create();
        $contract = Contract::create(['client_id' => $client->id, 'freelancer_id' => $freelancer->id, 'title' => 'متجر', 'status' => Contract::STATUS_ACTIVE]);
        $stage = $contract->stages()->create(['sort_order' => 1, 'title' => 'الوحيدة', 'deliverable' => 'x', 'duration_days' => 3, 'amount' => 100, 'revisions' => 2, 'status' => ContractStage::STATUS_UNFUNDED]);

        // Started at EasyKash, then abandoned.
        $this->actingAs($client)->post(route('contracts.fund', $contract), ['stages' => [$stage->id], 'source' => 'easykash'])->assertRedirect();
        $this->assertSame(ContractStage::STATUS_FUNDING, $stage->fresh()->status);
        $this->actingAs($freelancer)->post(route('contracts.fund.cancel', [$contract, $stage]))->assertForbidden();
        $this->actingAs($client)->post(route('contracts.fund.cancel', [$contract, $stage]))->assertSessionHasNoErrors();
        $this->assertSame(ContractStage::STATUS_UNFUNDED, $stage->fresh()->status);
        $this->assertSame(OnlinePayment::STATUS_CANCELLED, OnlinePayment::sole()->status);

        // Funded from the balance, then refunded by a decision: the project ends, not «completed».
        app(ContractLedger::class)->fundFromBalance($contract, [$stage->id], $client);
        app(ContractLedger::class)->refund($stage->fresh(), 'قرار');
        $this->assertSame(Contract::STATUS_CANCELLED, $contract->fresh()->status);
        $this->assertEquals(1000, $client->fresh()->balance);
    }
}
