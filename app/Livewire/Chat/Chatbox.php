<?php

namespace App\Livewire\Chat;

use App\Models\front\Conversation;
use App\Models\front\Message;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use App\Events\MessageSend;

class Chatbox extends Component
{

    public $reciever_user;

    public $selectedConversation;

    public $message_count;
    public $messages;
    public $conversation_id;
    //public $listeners = ['loadconversation','pushMessage'];

    public function getListeners()
    {
        $auth_id = Auth::id();
        return [
            "echo-private:chat.{$auth_id},MessageSend" => 'MessageReceived',
            'loadconversation', 'pushMessage','conversationSelected'
        ];
    }

    //protected $listeners = ['conversationSelected'];


    public function mount($conversation_id)
    {
        $this->conversation_id = $conversation_id;

       // $this->selectedConversation = Conversation::find($id);

        // العثور على المحادثة بناءً على المعرف
        $this->selectedConversation = Conversation::findOrFail($conversation_id);

        // العثور على المستخدم الآخر في المحادثة
        $this->reciever_user = User::find($this->selectedConversation->receiver_id === Auth::id()
            ? $this->selectedConversation->sender_id
            : $this->selectedConversation->receiver_id);

        // تحميل الرسائل
        $this->message_count = Message::where('conversation_id', $this->selectedConversation->id)->count();
        $this->messages = Message::where('conversation_id', $this->selectedConversation->id)->get();

        // تحديث حالة الرسائل إلى "مقروءة"
        Message::where('conversation_id', $this->selectedConversation->id)
            ->where('receiver_id', Auth::id())
            ->update(['red' => 1]);


        // تحديث الإشعارات إلى "مقروءة"
        $notification_type = 'App\Notifications\NewMessage';
        $notifications = Auth::user()->unreadNotifications->where('type', $notification_type);
        foreach ($notifications as $notification) {
            $notification->markAsRead();
        }
    }
    function MessageReceived($event)
    {
        // Refresh chat list
        $this->dispatch('refresh')->to('chat.chat-list');

        $broadcastedMessage = Message::find($event['message']);

        // dd($broadcastedMessage);
        // Check if message exists
        if (!$broadcastedMessage) {
            // Handle the case where the message is not found
            return;
        }

        // Check if any selected conversation is set
        if ($this->selectedConversation) {
            // Check if Auth/current selected conversation is same as broadcasted selected conversation
            if ((int) $this->selectedConversation->id === (int) $event['conversation_id']) {
                // Mark message as read
                $broadcastedMessage->red = 1; // Assumes you have a 'read' field in your messages table
                $broadcastedMessage->save();
                $this->pushMessage($broadcastedMessage->id);
                // Emit an event to the frontend

               // $this->dispatch('MessageRed')->self();
            }

        }
    }


    public function pushMessage($messageId)
    {
        $newMessage = Message::find($messageId);
        $this->messages->push($newMessage);
    }

    public function render()
    {
        return view('livewire.chat.chatbox');
    }


}
