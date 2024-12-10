<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Http\Traits\Message_Trait;
use App\Models\admin\Ticket;
use App\Models\admin\TicketMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class TicketController extends Controller
{
    use Message_Trait;
    public function index()
    {
        $tickets = Ticket::with('user')->orderBy('created_at','desc')->get();
        return view('admin.tickets.index',compact('tickets'));
    }

    public function details($ticket_id)
    {
        $messages = TicketMessage::where('ticket_id',$ticket_id)->get();
      return view('admin.tickets.details',compact('messages','ticket_id'));
    }

    public function create(Request $request, $ticket_id)
    {
        $ticket = Ticket::findOrFail($ticket_id);
        $user_id = $ticket['user_id'];
        if ($request->isMethod('POST')) {
            $data = $request->all();
           // dd($data);
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

            $message->user_id = $user_id;
            $message->ticket_id = $ticket_id;
            $message->content = $data['message'];
            $message->sender_type = 'support';
            $message->save();

            /////////// Update Ticket Status
            ///
            $ticket->update([
                're-send'=>1
            ]);
            DB::commit();

            return $this->success_message(' تم اضافة الرد بنجاح  ');
        }
    }
}
