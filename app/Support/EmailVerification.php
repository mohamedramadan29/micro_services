<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;

/**
 * Email confirmation by a signed link (valid 7 days). Confirming only sets
 * `email_verified_at`; it never touches `status`, so it cannot re-open an
 * account the admin deactivated.
 */
class EmailVerification
{
    public const VALID_DAYS = 7;

    public static function url(User $user): string
    {
        return URL::temporarySignedRoute('email.verify', now()->addDays(self::VALID_DAYS), [
            'user' => $user->id,
            'hash' => sha1((string) $user->email),
        ]);
    }

    public static function send(User $user): void
    {
        Mail::to($user->email)->send(new \App\Mail\VerifyEmail($user));
    }

    /** Marks the email confirmed when the link matches the user's current email. */
    public static function confirm(User $user, string $hash): bool
    {
        if (! hash_equals(sha1((string) $user->email), $hash)) {
            return false;
        }
        if (! $user->email_verified_at) {
            $user->forceFill(['email_verified_at' => now()])->save();
            $user->markFreelancerProfileCompleted();
        }

        return true;
    }
}
