<?php

namespace App\Http\Controllers\front;

use App\Http\Controllers\Controller;
use App\Models\admin\Service;
use App\Models\front\Conversation;
use App\Models\front\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;

class ConversationController extends Controller
{

    public function chats()
    {
        $userId = Auth::id();

        $conversations = Conversation::involving($userId)
            ->with([
                'lastMessage', // آخر رسالة لكل محادثة
                'sender',  // لجلب بيانات المرسل
                'receiver' // لجلب بيانات المستقبل
            ])
            ->orderByDesc('last_time_message')
            ->get();

        return view('website.chat.index', compact('conversations'));
    }
    public function start_conversation(Request $request)
    {
        $data = $request->validate(['service_id' => 'required|integer']);

        // The conversation is always with the owner of the service (never a user id sent by the browser).
        $service = Service::findOrFail($data['service_id']);
        $receiverId = (int) $service->user_id;
        if ($receiverId === (int) Auth::id()) {
            return Redirect::back()->withErrors(['error' => 'هذه خدمتك']);
        }
        $service_data = $service['name'];
        $checkConversation = Conversation::where('service_id', $service->id)
            ->where(fn ($q) => $q->where(fn ($a) => $a->where('sender_id', Auth::id())->where('receiver_id', $receiverId))
                ->orWhere(fn ($b) => $b->where('sender_id', $receiverId)->where('receiver_id', Auth::id())))
            ->first();
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

    public function consult_start_conversation(Request $request)
    {
        $data = $request->validate([
            'consultant' => 'required|integer|exists:users,id',
            'category' => 'required',
            'question' => 'required|string|max:2000',
        ], ['question.required' => 'اكتب سؤالك']);
        $receiverId = (int) $data['consultant'];
        if ($receiverId === (int) Auth::id()) {
            return Redirect::back()->withErrors(['error' => 'لا يمكنك مراسلة نفسك']);
        }
        $checkConversation = Conversation::where(fn ($q) => $q->where(fn ($a) => $a->where('sender_id', Auth::id())->where('receiver_id', $receiverId))
                ->orWhere(fn ($b) => $b->where('sender_id', $receiverId)->where('receiver_id', Auth::id())))
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
