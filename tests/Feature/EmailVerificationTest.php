<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\EmailVerification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

/** Email confirmation by a signed link; for freelancers it is their verification (client decision 2026-10-16). */
class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_signed_link_confirms_the_email_and_verifies_a_freelancer(): void
    {
        $user = User::factory()->pendingFreelancer()->create();
        $this->assertFalse($user->isVerifiedFreelancer());

        $this->get(EmailVerification::url($user))->assertRedirect(url('login'))->assertSessionHas('Success_message');
        $this->assertNotNull($user->fresh()->email_verified_at);
        $this->assertTrue($user->fresh()->isVerifiedFreelancer());
        $this->assertFalse($user->fresh()->isApprovedFreelancer()); // the admin still approves
    }

    public function test_forged_expired_or_old_links_are_refused(): void
    {
        $user = User::factory()->pendingFreelancer()->create();

        // Unsigned, with the right hash.
        $this->get('user/verify/' . $user->id . '/' . sha1($user->email))->assertForbidden();
        // Signed for another email (the user changed it since).
        $this->get(URL::temporarySignedRoute('email.verify', now()->addDay(), ['user' => $user->id, 'hash' => sha1('old@example.com')]))
            ->assertRedirect(url('login'))->assertSessionHasErrors();
        // Expired.
        $this->get(URL::temporarySignedRoute('email.verify', now()->subMinute(), ['user' => $user->id, 'hash' => sha1($user->email)]))->assertForbidden();
        $this->assertNull($user->fresh()->email_verified_at);
    }

    public function test_the_old_base64_link_no_longer_reopens_a_deactivated_account(): void
    {
        $user = User::factory()->create(['status' => 0]);

        $this->get('user/confirm/' . base64_encode($user->email))->assertRedirect(url('login'))->assertSessionHasErrors();
        $this->assertSame(0, (int) $user->fresh()->status);
        $this->assertNull(User::find($user->id)->getOriginal('admin_verified_at'));

        // Confirming by the new link never touches the account status either.
        $user->forceFill(['email_verified_at' => null])->save();
        $this->get(EmailVerification::url($user));
        $this->assertSame(0, (int) $user->fresh()->status);
    }

    public function test_a_new_link_is_sent_on_request_without_revealing_accounts(): void
    {
        Mail::fake();
        $user = User::factory()->pendingFreelancer()->create();

        $this->post(route('email.verify.resend'), ['email' => $user->email])->assertSessionHas('Success_message');
        $this->post(route('email.verify.resend'), ['email' => 'nobody@example.com'])->assertSessionHas('Success_message');
        $this->actingAs($user)->post(route('email.verify.resend'))->assertSessionHas('Success_message');
        $this->actingAs($user)->get('update-account')->assertOk()->assertSee('أعد إرسال رابط التأكيد');

        $user->forceFill(['email_verified_at' => now()])->save();
        $this->actingAs($user)->post(route('email.verify.resend'));

        // Sent for the registered email and the logged-in user only: never to unknown or confirmed emails.
        Mail::assertSentCount(2);
        Mail::assertSent(\App\Mail\VerifyEmail::class, fn ($mail) => $mail->hasTo($user->email) && str_contains($mail->render(), '/user/verify/' . $user->id . '/'));
    }

    public function test_the_admin_can_verify_by_hand_or_resend_the_link(): void
    {
        Mail::fake();
        $admin = User::factory()->admin()->create();
        $user = User::factory()->pendingFreelancer()->create();

        $this->actingAs($admin)->post(route('admin.freelancers.verify-email', $user))->assertSessionHas('success');
        $this->actingAs($admin)->post(route('admin.freelancers.verify', $user))->assertSessionHas('success');
        $this->assertTrue($user->fresh()->isVerifiedFreelancer());
        $this->actingAs($admin)->post(route('admin.freelancers.verify', $user));
        $this->assertFalse($user->fresh()->isVerifiedFreelancer());
    }
}
