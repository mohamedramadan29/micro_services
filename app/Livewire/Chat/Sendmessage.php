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

    public $listeners = ['UpdateSendMessage','dispatchMessageSend'];
    public function UpdateSendMessage(Conversation $converstion, User $reciever)
    {
        $this->selectedConversation = $converstion;
        $this->reciever_user = $reciever;
    }
    public function SendMessage()
    {
        if ($this->body == null) {
            return null;
        }
        $this->createMessage = Message::create([
            'conversation_id' => $this->selectedConversation->id,
            'sender_id' => Auth::id(),
            'receiver_id' => $this->reciever_user->id,
            'body' => $this->body,
        ]);
        $user = User::where('id', $this->reciever_user->id)->first();
        $this->selectedConversation->last_time_message = $this->createMessage->created_at;
        $this->selectedConversation->save();
        $this->body = '';
        $this->dispatch('pushMessage',$this->createMessage->id)->to('chat.Chatbox');
        $this->dispatch('refresh')->to('chat.chat-list');
        $this->reset('body');
        unset($this->body);
        $this->dispatch('dispatchMessageSend')->self();
        // Send Notification New Message To Reciever
        Notification::send($user, new NewMessage($this->selectedConversation->id, Auth::user()->user_name));

    }

    public function dispatchMessageSend()
    {
        broadcast(new MessageSend(Auth::user(),$this->selectedConversation,$this->createMessage,$this->reciever_user));
    }
    public function render()
    {
        return view('livewire.chat.sendmessage');
    }
}
