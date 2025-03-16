<?php

namespace App\Http\Controllers\front;

use App\Http\Controllers\Controller;
use App\Http\Traits\Message_Trait;
use App\Http\Traits\Upload_Images;
use App\Models\admin\TicketMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class TicketMessageController extends Controller
{

    use Message_Trait;
    use Upload_Images;
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
                'message' => 'required|string',
                'file' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            ];
            $messages = [
                'message.required' => 'من فضلك ادخل رسالتك ',
                'message.string' => ' من فضلك ادخل نص الرسالة بشكل صحيح  ',
                'file.image' => 'يجب أن يكون الملف صورة',
                'file.mimes' => 'يجب أن يكون الملف صورة',
                'file.max' => 'يجب أن يكون حجم الملف أقل من 2 ميجابايت',
            ];
            $validator = Validator::make($data, $rules, $messages);
            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator)->withInput();
            }
            $filename = '';
            if ($request->hasFile('file')) {
                $file = $request->file('file');
                $filename = $this->saveImage($file, public_path('assets/uploads/tickets'));
            }
            DB::beginTransaction();

            $message = new TicketMessage();
            $message->user_id = Auth::user()->id;
            $message->ticket_id = $ticket_id;
            $message->content = $data['message'];
            $message->sender_type = 'user';
            $message->file = $filename;
            $message->save();

            DB::commit();

            return $this->success_message(' تم اضافة رسالتك بنجاح  ');
        }
    }
}
