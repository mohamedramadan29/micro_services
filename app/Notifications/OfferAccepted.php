<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OfferAccepted extends Notification
{
    use Queueable;

    protected $user_id;
    protected $user_name;
    protected $project_title;
    protected $project_id;
    protected $project_slug;
    public function __construct($user_id, $user_name, $project_title, $project_id, $project_slug)
    {
        $this->user_id = $user_id;
        $this->user_name = $user_name;
        $this->project_title = $project_title;
        $this->project_id = $project_id;
        $this->project_slug = $project_slug;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(' تمت الموافقة علي العرض الخاص بك  ')
            ->greeting(' مرحبا !! تمت الموافقة علي العرض الخاص بك علي المشروع  ')
            ->line('  عنوان المشروع  ' . $this->project_title)
            ->action(' تفاصيل المشروع  ', url('/project/' . $this->project_id . '/' . $this->project_slug))
            ->line('  تحياتنا  - منصة نفذها  ');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'user_id'=>$this->user_id,
            'user_name' => $this->user_name,
            'project_title' => $this->project_title,
            'project_id' => $this->project_id,
            'project_slug' => $this->project_slug,
        ];
    }
}
