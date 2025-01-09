<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AdminActiveCourse extends Notification
{
    use Queueable;
    protected $user , $course_id , $course_slug , $course_title;
    public function __construct($user, $course_id , $course_slug , $course_title)
    {
        $this->user = $user;
        $this->course_id = $course_id;
        $this->course_slug = $course_slug;
        $this->course_title = $course_title;
    }
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
                    ->subject(' تم تفعيل الدورة ' . $this->course_title)
                    ->greeting('مرحباً!')
                    ->line(' رائع !! تم تفعيل الدورة  :: ' . $this->course_title)
                    ->action('مشاهدة الدورة', url('/course/' . $this->course_id . '-' . $this->course_slug))
                    ->line(' تحياتنا لك عزيزي ');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'user_id' => $this->user,
            'course_id' => $this->course_id,
            'course_slug' => $this->course_slug,
            'course_title' => $this->course_title,
            'noti_title' => ' رائع تم تفعيل الدورة  ',
        ];
    }
}
