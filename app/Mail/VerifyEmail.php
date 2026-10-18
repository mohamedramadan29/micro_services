<?php

namespace App\Mail;

use App\Models\User;
use App\Support\EmailVerification;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/** The signed email-confirmation link (App\Support\EmailVerification). */
class VerifyEmail extends Mailable
{
    public function __construct(public User $user)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'تأكيد البريد الإلكتروني لحسابك في نفّذها');
    }

    public function content(): Content
    {
        return new Content(view: 'website.emails.UserActivation', with: [
            'name' => $this->user->name,
            'url' => EmailVerification::url($this->user),
        ]);
    }
}
