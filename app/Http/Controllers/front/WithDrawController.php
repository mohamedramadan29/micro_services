<?php

namespace App\Http\Controllers\front;

use App\Http\Traits\Message_Trait;
use App\Models\front\WithDraw;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;

class WithDrawController extends Controller
{
    use Message_Trait;
    public function WithDraw(Request $request)
    {
        $data = $request->all();
        $user = User::where('id', Auth::id())->first();
        $rules = [
            'amount' => 'required|numeric|min:1|max:' . $user->balance,
            'method' => 'required',
            'country'=>'required',
            'account_name'=>'required',
            'account_number'=>'required',
            'bank_name'=>'required',
            'swift_code'=>'required',
            'iban_code'=>'required',
        ];
        $messages = [
            'amount.required' => ' من فضلك حدد الميلغ  ',
            'amount.numeric' => ' من فضلك ادخل مبلغ صحيح للسحب  ',
            'amount.min' => ' اقل مبلغ للسحب هو 1 دولار  ',
            'amount.max' => ' الرصيد المتاح للسحب اقل من المبلغ المطلوب  ',
            'method.required' => ' من فضلك حدد طريقة السحب  ',
            'country.required' => ' من فضلك حدد الدولة  ',
            'account_name.required' => ' من فضلك حدد اسم الحساب  ',
            'account_number.required' => ' من فضلك حدد رقم الحساب  ',
            'bank_name.required' => ' من فضلك حدد اسم البنك  ',
            'swift_code.required' => ' من فضلك حدد كود السويفت  ',
            'iban_code.required' => ' من فضلك حدد كود الايبان  ',
        ];
        $withdraw_transactions = WithDraw::where('user_id',Auth::id())->where('status',0)->get();
        if($withdraw_transactions->count() > 0){
            return Redirect()->back()->withErrors(' هناك عملية سحب في الوقت الحالي من فضلك انتظر حتي اتمام العملية الاولي  ');
        }
        if($user->balance < $request->amount){
            return Redirect()->back()->withErrors(' الرصيد المتاح للسحب اقل من المبلغ المطلوب  ');
        }
        $validator = Validator::make($data, $rules, $messages);
        if ($validator->fails()) {
            return Redirect::back()->withErrors($validator)->withInput();
        }

        DB::beginTransaction();
        $withdraw = new WithDraw();
        $withdraw->user_id = Auth::id();
        $withdraw->amount = $data['amount'];
        $withdraw->method = $data['method'];
        $withdraw->account_name = $data['account_name'];
        $withdraw->account_number = $data['account_number'];
        $withdraw->bank_name = $data['bank_name'];
        $withdraw->swift_code = $data['swift_code'];
        $withdraw->iban_code = $data['iban_code'];
        $withdraw->country = $data['country'];
        $withdraw->status = 0;
        $withdraw->save();
        ################## Update User Balance ############################
        $user->balance -= $request->amount;
        $user->save();
        DB::commit();
        return $this->success_message(' تم اضافة طلب السحب الخاص بك بنجاح  ');
    }
}
