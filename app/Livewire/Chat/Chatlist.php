<?php

namespace App\Livewire\Chat;

use App\Models\front\Conversation;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Chatlist extends Component
{

    public $sender_id;

    public $conversations;

    public $reciever_user;

    public $selectedConversation;

    //protected $listeners = ['ChatUserSelected','refresh'=>'$refresh','resetComponent'];

    public function mount()
    {
        $this->sender_id = Auth::id();

        $this->conversations = Conversation::where('sender_id', $this->sender_id)->
        orWhere('receiver_id', $this->sender_id)->get();
        //dd($this->conversations);

    }
    ////////// Get The User Instance Or Receiver User
    ///
    public function getuserinstance(Conversation $conversation, $request)
    {
        if ($conversation->sender_id == Auth::id()) {
            $this->reciever_user = User::where('id', $conversation->receiver_id)->first();
        } else {
            $this->reciever_user = User::where('id', $conversation->sender_id)->first();
        }
        if (isset($request)) {
            return $this->reciever_user->$request;
        }
    }

    /////////// Get The Chat User Selected
    ///
    public function ChatUserSelected($conversationId, $receiverId)
    {
        $this->selectedConversation = Conversation::find($conversationId);
        $this->reciever_user = User::find($receiverId);
        $this->dispatch('loadconversation',$this->selectedConversation,$this->reciever_user)->to('chat.Chatbox');
        $this->dispatch('UpdateSendMessage',$this->selectedConversation,$this->reciever_user)->to('chat.Sendmessage');

    }

    public function render()
    {
        return view('livewire.chat.chatlist');
    }
}
