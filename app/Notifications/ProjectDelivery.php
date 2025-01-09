<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ProjectDelivery extends Notification
{
    use Queueable;

    protected $user, $project_offer, $project_id, $project_slug, $project_title;
    public function __construct($user , $project_offer , $project_id , $project_slug , $project_title)
    {
        $this->user = $user;
        $this->project_offer = $project_offer;
        $this->project_id = $project_id;
        $this->project_slug = $project_slug;
        $this->project_title = $project_title;
    }
    public function via(object $notifiable): array
    {
        return ['mail','database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(' تم تسليم المشروع  ')
            ->greeting('مرحباً!')
            ->line(' رائع !! تم تسليم المشروع بنجاح :: ' . $this->project_title)
            ->action(' مشاهدة المشروع  ', url('project/' . urlencode($this->project_id) . '-' . urlencode($this->project_slug)))
            ->line(' تحياتنا لك عزيزي ');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'user_id' => $this->user,
            'project_id' => $this->project_id,
            'project_slug' => $this->project_slug,
            'project_title' => $this->project_title,
            'noti_title' => ' تم تسليم المشروع  ',
        ];
    }
}
