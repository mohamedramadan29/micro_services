<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ConversationController extends Controller
{
    public function start_conversation(Request $request)
    {
        $data = $request->all();
        $receiverId = $data['receiver_id'];
        $checkConversation = Conversation::where('sender_id', Auth::id())->where('receiver_id', $receiverId)
            ->Orwhere('sender_id', $receiverId)->where('receiver_id', Auth::id())
            ->get();
        if (count($checkConversation) == 0) {
            //  dd('No Conversation');
            // //// Create Chat
            $createConversation = Conversation::create([
                'sender_id' => Auth::id(),
                'receiver_id' => $receiverId,
                'service_id'=>$data['service_id'],
            ]);
            // Create Message
            $createMessage = Message::create([
                'conversation_id'=>$createConversation->id,
                'sender_id' => Auth::id(),
                'receiver_id' => $receiverId,
                'body'=>"Hello This Is First Message",
            ]);
            $createConversation->last_time_message = $createMessage->created_at;
            $createConversation->save();
            dd('saved');
        } elseif (count($checkConversation)  > 0) {
            dd('conversation sataty');
        }
    }
}
