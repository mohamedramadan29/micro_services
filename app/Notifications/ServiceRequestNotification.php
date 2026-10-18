<?php

namespace App\Notifications;

use App\Services\ServiceRequests\NotificationSettings;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Base for request/offer notifications. The bell entry (database) is written
 * immediately; the optional email goes through the queue so a slow or broken
 * mail server never blocks the request that triggered it.
 * Data: text (one line for the bell) and url (a site path, opened via
 * notifications.open which marks it read).
 */
abstract class ServiceRequestNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /** Email switch in the site settings (NotificationSettings::EMAILS); null = bell only. */
    protected ?string $emailKey = null;

    abstract protected function text(): string;

    abstract protected function path(): string;

    public function via(object $notifiable): array
    {
        $email = $this->emailKey && filled($notifiable->email) && NotificationSettings::emailEnabled($this->emailKey);

        return $email ? ['database', 'mail'] : ['database'];
    }

    public function viaConnections(): array
    {
        return ['database' => 'sync'];
    }

    public function toArray(object $notifiable): array
    {
        return ['text' => $this->text(), 'url' => $this->path()];
    }

    /** A queued email that could not be sent (after its tries): kept in the delivery log. */
    public function failed(\Throwable $e): void
    {
        try {
            \App\Models\NotificationLog::create(['notification' => class_basename($this), 'channel' => 'mail', 'status' => 'failed',
                'text' => mb_substr($this->text(), 0, 500), 'error' => mb_substr($e->getMessage(), 0, 500)]);
        } catch (\Throwable) {
        }
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject($this->text())
            ->greeting('مرحبًا ' . $notifiable->name)
            ->line($this->text())
            ->action('عرض التفاصيل', url($this->path()));
    }
}
