<?php

namespace App\Listeners;

use App\Models\NotificationLog;
use Illuminate\Notifications\Events\NotificationFailed;
use Illuminate\Notifications\Events\NotificationSent;

/** Records every notification delivery (bell and email): sent or failed with the reason. Never breaks sending. */
class LogNotificationDelivery
{
    public function handle(NotificationSent|NotificationFailed $event): void
    {
        try {
            $notifiable = $event->notifiable;
            $text = method_exists($event->notification, 'toArray') ? ($event->notification->toArray($notifiable)['text'] ?? null) : null;
            $error = $event instanceof NotificationFailed ? ($event->data['exception'] ?? null) : null;
            NotificationLog::create([
                'notification' => class_basename($event->notification),
                'channel' => mb_substr((string) $event->channel, 0, 30),
                'status' => $event instanceof NotificationFailed ? 'failed' : 'sent',
                'notifiable_id' => $notifiable->id ?? null,
                'recipient' => mb_substr((string) ($notifiable->name ?? implode(', ', (array) ($notifiable->routes['mail'] ?? []))), 0, 255) ?: null,
                'text' => is_string($text) ? mb_substr($text, 0, 500) : null,
                'error' => $error ? mb_substr($error instanceof \Throwable ? $error->getMessage() : (string) $error, 0, 500) : null,
            ]);
        } catch (\Throwable) {
            // A log failure must never stop a notification.
        }
    }
}
