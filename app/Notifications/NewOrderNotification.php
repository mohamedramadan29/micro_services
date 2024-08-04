<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewOrderNotification extends Notification
{
    use Queueable;
    public $seller_id,$seller_name,$seller_username;
    public $buyer_id,$buyer_name,$buyer_username,$serv_id,$serv_slug,$serv_name;

    public function __construct($buyer_id,$buyer_name,$buyer_username,$serv_id,$serv_slug,$serv_name)
    {
//        $this->seller_id = $seller_id;
//        $this->seller_username = $seller_username;
//        $this->seller_name = $seller_name;
         $this->buyer_id = $buyer_id;
         $this->buyer_name = $buyer_name;
         $this->buyer_username = $buyer_username;
         $this->serv_id=$serv_id;
         $this->serv_slug = $serv_slug;
         $this->serv_name = $serv_name;
    }

    public function via(object $notifiable): array
    {
        return ['mail','database'];
    }
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(' طلب خدمة جديد ')
            ->greeting(' مرحبا !! لديك طلب خدمة جديد ')
                    ->line($this->buyer_name . ' طلب الخدمة الخاصة بك  ' . $this->serv_name)
                    ->action(' مشاهدة الطلب  ', url('/orders'))
                    ->line(' تحياتنا لك  ');
    }
    public function toArray(object $notifiable): array
    {
        return [
//            'seller_id'=>$this->seller_id,
//            'seller_username'=>$this->seller_username,
//            'seller_name'=>$this->seller_name,
            'buyer_id'=>$this->buyer_id,
            'buyer_name'=>$this->buyer_name,
            'buyer_username'=>$this->buyer_username,
            'serv_id'=>$this->serv_id,
            'serv_name'=>$this->serv_name,
            'noti_title'=> ' مرحبا !!  طلب خدمتك  ',
        ];
    }
}
