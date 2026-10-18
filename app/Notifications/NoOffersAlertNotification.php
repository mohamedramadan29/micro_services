<?php

namespace App\Notifications;

use App\Models\ServiceRequest;
use Illuminate\Notifications\Messages\MailMessage;

/** To admins: a published request got no offer within an hour (sent once per request). */
class NoOffersAlertNotification extends ServiceRequestNotification
{
    protected ?string $emailKey = 'admin_alerts';

    public function __construct(protected ServiceRequest $request)
    {
    }

    protected function text(): string
    {
        return \App\Support\Msg::t('admin.request_no_offers', ['number' => $this->request->number, 'service' => $this->request->service_name]);
    }

    protected function path(): string
    {
        return '/admin/requests/' . $this->request->number;
    }

    public function toArray(object $notifiable): array
    {
        return parent::toArray($notifiable) + [
            'kind' => 'no_offers',
            'request' => $this->request->number,
            // Spec page 7: the alert itself shows who and when.
            'details' => 'العميل: ' . $this->request->contact_name . ' · ' . $this->request->category_name . ' ← ' . $this->request->service_name
                . ' · نُشر ' . $this->request->published_at?->format('Y-m-d H:i') . ' · العروض: 0',
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $request = $this->request;

        return (new MailMessage)
            ->subject($this->text())
            ->greeting('تنبيه متابعة')
            ->line('اسم العميل: ' . $request->contact_name)
            ->line('الطلب: ' . $request->service_name . ' — ' . $request->number)
            ->line('التصنيف: ' . $request->category_name . ' ← ' . $request->service_name)
            ->line('وقت النشر: ' . $request->published_at?->format('Y-m-d H:i'))
            ->line('العروض حتى الآن: 0')
            ->action('افتح الطلب في لوحة الأدمن', url($this->path()));
    }
}
