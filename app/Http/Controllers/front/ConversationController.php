<?php

namespace App\Http\Controllers\front;

use App\Http\Controllers\Controller;
use App\Models\admin\Service;
use App\Models\front\Conversation;
use App\Models\front\Message;
use App\Models\front\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;

class ConversationController extends Controller
{

    public function chats()
    {
        $userId = Auth::id();

        $conversations = Conversation::where('sender_id', $userId)
        ->orWhere('receiver_id', $userId)
        ->with([
            'messages' => function ($query) {
                $query->latest()->take(1); // جلب آخر رسالة فقط
            },
            'sender',  // لجلب بيانات المرسل
            'receiver' // لجلب بيانات المستقبل
        ])
        ->get();

        return view('website.chat.index', compact('conversations'));
    }
    public function start_conversation(Request $request)
    {
        $data = $request->all();
        $receiverId = $data['receiver_id'];
        /////////////  Get The Services Data
        ///
        $service = Service::findOrFail($data['service_id']);
        $service_data = $service['name'];
        $checkConversation = Conversation::where('sender_id', Auth::id())->where('receiver_id', $receiverId)
            ->Orwhere('sender_id', $receiverId)->where('receiver_id', Auth::id())
            ->where('service_id', $data['service_id'])->first();
        if (!$checkConversation) {
            // //// Create Chat
            $createConversation = Conversation::create([
                'sender_id' => Auth::id(),
                'receiver_id' => $receiverId,
                'service_id' => $data['service_id'],
            ]);
            // Create Message
            $createMessage = Message::create([
                'conversation_id' => $createConversation->id,
                'sender_id' => Auth::id(),
                'receiver_id' => $receiverId,
                'body' => $service_data . " تواصل بسبب طلب الخدمة  ",
            ]);
            $createConversation->last_time_message = $createMessage->created_at;
            $createConversation->save();
            return Redirect::to('chat-main/' . $createConversation->id);
        } else {
            return Redirect::to('chat-main/' . $checkConversation->id);
        }
    }

    public function project_start_conversation(Request $request)
    {
        $data = $request->all();
        $receiverId = $data['receiver_id'];

        // الحصول على بيانات المشروع
        $project = Project::findOrFail($data['project_id']);
        $project_title = $project->title;
        $checkConversation = Conversation::where('sender_id', Auth::id())->where('receiver_id', $receiverId)
            ->Orwhere('sender_id', $receiverId)->where('receiver_id', Auth::id())
            ->where('project_id', $data['project_id'])->first();

        if (!$checkConversation) {
            // إنشاء المحادثة الجديدة
            $createConversation = Conversation::create([
                'sender_id' => Auth::id(),
                'receiver_id' => $receiverId,
                'project_id' => $data['project_id'],
                'chat_type' => 'مشروع',
            ]);

            // إنشاء الرسالة الأولى
            $createMessage = Message::create([
                'conversation_id' => $createConversation->id,
                'sender_id' => Auth::id(),
                'receiver_id' => $receiverId,
                'body' => $project_title . " تواصل بسبب طلب الخدمة ",
            ]);

            // تحديث وقت آخر رسالة
            $createConversation->last_time_message = $createMessage->created_at;
            $createConversation->save();

            return Redirect::to('chat-main/' . $createConversation->id);
        } else {
            // إعادة التوجيه إلى المحادثة الموجودة
            return Redirect::to('chat-main/' . $checkConversation->id);
        }
    }

    public function consult_start_conversation(Request $request)
    {
        $data = $request->all();
        $receiverId = $data['consultant'];
        // dd($data);
        $checkConversation = Conversation::where('sender_id', Auth::id())->where('receiver_id', $receiverId)
            ->Orwhere('sender_id', $receiverId)->where('receiver_id', Auth::id())
            ->where('consultant_id', $receiverId)
            ->where('specialist_id', $data['category'])
            ->where('chat_type', 'استشارة')
            ->first();
        if (!$checkConversation) {
            // إنشاء المحادثة الجديدة
            $createConversation = Conversation::create([
                'sender_id' => Auth::id(),
                'receiver_id' => $receiverId,
                'consultant_id' => $receiverId,
                'specialist_id' => $data['category'],
                'chat_type' => 'استشارة',
            ]);

            // إنشاء الرسالة الأولى
            $createMessage = Message::create([
                'conversation_id' => $createConversation->id,
                'sender_id' => Auth::id(),
                'receiver_id' => $receiverId,
                'body' => $data['question'] . " استشارة  ",
            ]);

            // تحديث وقت آخر رسالة
            $createConversation->last_time_message = $createMessage->created_at;
            $createConversation->save();

            return Redirect::to('chat-main/' . $createConversation->id);
        } else {
            // إعادة التوجيه إلى المحادثة الموجودة
            return Redirect::to('chat-main/' . $checkConversation->id);
        }
    }




}



