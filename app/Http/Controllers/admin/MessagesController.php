<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\front\Conversation;
use App\Models\front\Message;
use Illuminate\Http\Request;

class MessagesController extends Controller
{
    public function index()
    {
        $chats = Conversation::orderBy('last_time_message','desc')->paginate(10);
        return view('admin.messages.chats',compact('chats'));
    }

    public function chatDetails($id)
    {

        $chat = Conversation::find($id);
        $messages = Message::where('conversation_id',$id)->orderBy('created_at','desc')->get();
      //  dd($chat);
        return view('admin.messages.chat-details',compact('chat','messages'));
    }
}
