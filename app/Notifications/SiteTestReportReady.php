<?php

namespace App\Notifications;

use App\Models\SiteTest;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/** To the client (any email, no account needed): the team's report on their site check is ready. */
class SiteTestReportReady extends Notification
{
    public function __construct(protected SiteTest $test)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('تقرير فحص موقعك ' . $this->test->host . ' جاهز')
            ->greeting('مرحبًا ' . ($this->test->contact_name ?: ''))
            ->line('راجع فريق نفّذها نتيجة فحص موقعك وجهّز لك أهم الأولويات.')
            ->action('عرض التقرير', route('site-tests.show', ['siteTest' => $this->test->number, 't' => $this->test->token]));
    }
}
