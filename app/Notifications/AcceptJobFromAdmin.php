<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AcceptJobFromAdmin extends Notification
{
    use Queueable;

    public $reciever_id;
    public $serv_id;
    public $serv_slug;
    public $serv_name;

    public function __construct($reciever_id, $serv_id, $serv_slug, $serv_name)
    {
        $this->reciever_id = $reciever_id;
        $this->serv_id = $serv_id;
        $this->serv_slug = $serv_slug;
        $this->serv_name = $serv_name;

    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(' الموافقة علي الخدمة  ')
            ->greeting('مرحباً!')
            ->line(' رائع !! تمت الموافقة علي الخدمة الخاصة بك :: ' . $this->serv_name)
            ->action(' مشاهدة الخدمة  ', url('service/' . $this->serv_id . '-' . $this->serv_slug))
            ->line(' تحياتنا لك عزيزي ');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'reciever_id' => $this->reciever_id,
            'serv_id' => $this->serv_id,
            'serv_slug' => $this->serv_slug,
            'serv_name' => $this->serv_name,
            'noti_title' => ' رائع تمت الموافقة علي الخدمة الخاصة بك  ',
        ];
    }
}
