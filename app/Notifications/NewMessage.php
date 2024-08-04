<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewMessage extends Notification
{
    use Queueable;

    private $conversation_id;
    private $sender_username;

    public function __construct($conversation_id,$sender_username)
    {
        $this->sender_username = $sender_username;
        $this->conversation_id = $conversation_id;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }


    public function toArray(object $notifiable): array
    {
        return [
            'sender_username'=>$this->sender_username,
            'conversation_id'=>$this->conversation_id,
            'title'=>' لديك رسالة جديدة من ::  ',

        ];
    }
}
