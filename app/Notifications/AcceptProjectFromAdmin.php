<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AcceptProjectFromAdmin extends Notification
{
    use Queueable;


    public $user_id;
    public $project_id;
    public $project_slug;
    public $project_name;

    public function __construct($user_id , $project_id , $project_slug , $project_name)
    {
        $this->user_id = $user_id;
        $this->project_id = $project_id;
        $this->project_slug = $project_slug;
        $this->project_name = $project_name;
    }
    public function via(object $notifiable): array
    {
        return ['mail','database'];
    }
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
        ->subject(' الموافقة علي المشروع  ')
        ->greeting('مرحباً!')
        ->line(' رائع !! تمت الموافقة علي المشروع الخاصة بك :: ' . $this->project_name)
        ->action(' مشاهدة المشروع  ', url('project/' . urlencode($this->project_id) . '-' . urlencode($this->project_slug)))
        ->line(' تحياتنا لك عزيزي ');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'user_id' => $this->user_id,
            'project_id' => $this->project_id,
            'project_slug' => $this->project_slug,
            'project_name' => $this->project_name,
            'noti_title' => ' رائع تمت الموافقة علي المشروع الخاصة بك  ',
        ];
    }
}
