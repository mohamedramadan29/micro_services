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

    //public $listeners = ['loadconversation','pushMessage'];

    public function getListeners()
    {
        $auth_id = Auth::id();
        return [
            "echo-private:chat.{$auth_id},MessageSend" => 'MessageReceived',
            'loadconversation', 'pushMessage'
        ];
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
    public function loadconversation(Conversation $conversation,User $reciever)
    {
        $this->selectedConversation = $conversation;
        $this->reciever_user = $reciever;
        $this->message_count = Message::where('conversation_id', $this->selectedConversation->id)->count();
        $this->messages = Message::where('conversation_id', $this->selectedConversation->id)->get();
        //$this->dispatch('chatSelected');

        /// Make Update Red status to Readed
        Message::where('conversation_id',$this->selectedConversation->id)
            ->where('receiver_id',auth()->user()->id)->update(['red'=> 1]);

        ///////Make the Message Notitifcation IS Read


        /////// Make Notification Is Read
        ///
        $notification_type = 'App\Notifications\NewMessage';
        $notifications = Auth::user()->unreadNotifications->where('type', $notification_type);
        foreach ($notifications as $notification){
            $notification->markAsRead();
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
