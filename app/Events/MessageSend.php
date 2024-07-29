<?php

namespace App\Events;

use App\Models\front\Conversation;
use App\Models\front\Message;
use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageSend implements ShouldBroadcast
{

    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $sender;
    public $conversation;
    public $message;
    public $reciever;

    public function __construct(User $sender,Conversation $conversation,Message $message,User $reciever)
    {
        $this->sender = $sender;

        $this->reciever = $reciever;

        $this->conversation = $conversation;

        $this->message = $message;

    }

    public function broadcastWith()
    {
        return [
            'sender_id' => $this->sender->id,
            'message' => $this->message->id,
            'conversation_id' => $this->conversation->id,
            'receiver_id' => $this->reciever->id,
        ];
    }
    public function broadcastOn()
    {
        error_log($this->sender);
        error_log($this->reciever);
        return new PrivateChannel('chat.' . $this->reciever->id);
    }
}
