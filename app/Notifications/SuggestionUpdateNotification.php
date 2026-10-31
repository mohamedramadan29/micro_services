<?php

namespace App\Notifications;

use App\Models\Suggestion;
use App\Services\ServiceRequests\NotificationSettings;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Notifications\Messages\MailMessage;

/**
 * To the sender of a suggestion: received, or its status became planned / done / closed.
 * An account gets the bell entry (+ email by setting); a visitor who asked for updates gets the email only.
 */
class SuggestionUpdateNotification extends ServiceRequestNotification
{
    protected ?string $emailKey = 'suggestions';

    /** $event = received | planned | done | closed */
    public function __construct(protected Suggestion $suggestion, protected string $event)
    {
    }

    public function via(object $notifiable): array
    {
        if ($notifiable instanceof AnonymousNotifiable) {
            return NotificationSettings::emailEnabled($this->emailKey) ? ['mail'] : [];
        }

        return parent::via($notifiable);
    }

    protected function text(): string
    {
        return \App\Support\Msg::t('suggestions.' . $this->event, [
            'number' => $this->suggestion->reference_code, 'title' => $this->suggestion->title,
            'reason' => (string) $this->suggestion->status_reason,
        ]);
    }

    protected function path(): string
    {
        return $this->suggestion->user_id ? '/my/suggestions' : '/suggestions';
    }

    public function toMail(object $notifiable): MailMessage
    {
        $name = $notifiable instanceof AnonymousNotifiable ? ($this->suggestion->guest_name ?: '') : $notifiable->name;
        $mail = (new MailMessage)
            ->subject('نفّذها — اقتراحك ' . $this->suggestion->reference_code)
            ->greeting(trim('مرحبًا ' . $name))
            ->line($this->text());

        return $this->suggestion->user_id ? $mail->action('متابعة اقتراحاتي', url($this->path())) : $mail;
    }
}
