<?php

namespace App\Http\Controllers\admin;

use App\Http\Traits\Message_Trait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\admin\TicketMessage;
use App\Http\Controllers\Controller;
use App\Models\admin\Ticket;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class TicketMessageController extends Controller
{

    use Message_Trait;
    public function index($ticket_id)
    {

        $messages = TicketMessage::with('user')->where('ticket_id', $ticket_id)->get();
        return view('website.user.tickets.ticket-details', compact('messages', 'ticket_id'));
    }
    public function store(Request $request, $ticket_id)
    {
        if ($request->isMethod('POST')) {
            $data = $request->all();
            //dd($data);

            $rules = [
                'message' => 'required|string|min:10'
            ];
            $messages = [
                'message.required' => 'من فضلك ادخل رسالتك ',
                'message.min' => 'اقل احرف للرسالة هي 10 ارقام ',
                'message.string' => ' من فضلك ادخل نص الرسالة بشكل صحيح  ',
            ];
            $validator = Validator::make($data, $rules, $messages);
            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator)->withInput();
            }
            DB::beginTransaction();
            $message = new TicketMessage();

            $message->user_id = Auth::user()->id;
            $message->ticket_id = $ticket_id;
            $message->content = $data['message'];
            $message->sender_type = 'user';
            $message->save();

            DB::commit();

            return $this->success_message(' تم اضافة رسالتك بنجاح  ');
        }
    }
}
