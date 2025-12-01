<?php

namespace App\Livewire\Chat;

use App\Events\MessageSend;
use App\Models\front\Conversation;
use App\Models\front\Message;
use App\Models\User;
use App\Notifications\NewMessage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use Livewire\Component;

class Sendmessage extends Component
{
    public $selectedConversation;
    public $reciever_user;

    public $body;

    public $createMessage;

    public $conversation_id;

    public $listeners = ['UpdateSendMessage', 'dispatchMessageSend'];

    public function mount($conversation_id)
    {
        $this->conversation_id = $conversation_id;
        $this->loadConversationDetails();
    }

    private function loadConversationDetails()
    {
        $conversation = Conversation::findOrFail($this->conversation_id);

        // تحديد المستخدم المستلم
        $this->reciever_user = $conversation->sender_id === Auth::id()
            ? User::findOrFail($conversation->receiver_id)
            : User::findOrFail($conversation->sender_id);
    }


    public function updateConversationDetails($conversation_id)
    {
        $this->conversation_id = $conversation_id;

        // إعادة تحميل بيانات المحادثة
        $this->loadConversationDetails();
    }

    public function SendMessage()
    {
        // التأكد من أن الرسالة ليست فارغة
        if (empty($this->body)) {
            return;
        }

        // إنشاء الرسالة
        $message = Message::create([
            'conversation_id' => $this->conversation_id,
            'sender_id' => Auth::id(),
            'receiver_id' => $this->reciever_user->id,
            'body' => $this->body,
        ]);
        $this->reset('body');
        $this->dispatch('clearMessageInput');
        $this->dispatch('scrollMessages');
        // تحديث وقت آخر رسالة في المحادثة
        $conversation = Conversation::findOrFail($this->conversation_id);
        $conversation->last_time_message = $message->created_at;
        $conversation->save();
        // إرسال الإشعار للمستلم
        Notification::send($this->reciever_user, new NewMessage($this->conversation_id, Auth::user()->user_name));
        // إرسال الحدث عبر الـ Broadcast
        broadcast(new MessageSend(Auth::user(), $conversation, $message, $this->reciever_user));
        // إعادة تعيين الرسالة
        $this->reset('body');
        // تحديث المكونات الأخرى
        $this->dispatch('pushMessage', $message->id)->to('chat.chatbox');
    }

    public function dispatchMessageSend()
    {
        broadcast(new MessageSend(Auth::user(), $this->selectedConversation, $this->createMessage, $this->reciever_user));
    }
    public function render()
    {
        return view('livewire.chat.sendmessage');
    }
}
