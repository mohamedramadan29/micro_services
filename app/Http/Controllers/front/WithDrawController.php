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
        $method = (string) $request->input('method');
        $rules = [
            'amount' => 'required|numeric|min:1|max:' . $user->balance,
            'method' => 'required|in:' . implode(',', array_keys(WithDraw::METHODS)),
            'country' => 'nullable|string|max:100',
            'account_name' => 'required|string|max:255',
            'account_number' => 'required|string|max:255',
            // A bank transfer needs the bank; IBAN/SWIFT only for transfers outside Egypt (optional).
            'bank_name' => ($method === 'bank' ? 'required' : 'nullable') . '|string|max:255',
            'swift_code' => 'nullable|string|max:50',
            'iban_code' => 'nullable|string|max:50',
        ];
        $messages = [
            'amount.required' => ' من فضلك حدد المبلغ  ',
            'amount.numeric' => ' من فضلك ادخل مبلغ صحيح للسحب  ',
            'amount.min' => ' اقل مبلغ للسحب هو 1 جنيه  ',
            'amount.max' => ' الرصيد المتاح للسحب اقل من المبلغ المطلوب  ',
            'method.required' => ' من فضلك حدد طريقة السحب  ',
            'method.in' => ' طريقة السحب غير صحيحة  ',
            'account_name.required' => ' من فضلك اكتب اسم صاحب الحساب  ',
            'account_number.required' => $method === 'bank' ? ' من فضلك اكتب رقم الحساب البنكي  ' : ' من فضلك اكتب رقم المحفظة أو عنوان إنستاباي  ',
            'bank_name.required' => ' من فضلك اكتب اسم البنك  ',
        ];
        $validator = Validator::make($data, $rules, $messages);
        if ($validator->fails()) {
            return Redirect::back()->withErrors($validator)->withInput();
        }

        // One pending withdrawal at a time and the balance check happen under a row lock, so two
        // simultaneous requests cannot both pass (the deduction is atomic).
        try {
            DB::transaction(function () use ($data) {
                $user = User::whereKey(Auth::id())->lockForUpdate()->first();
                if (WithDraw::where('user_id', $user->id)->where('status', 0)->exists()) {
                    throw new \RuntimeException(' هناك عملية سحب في الوقت الحالي من فضلك انتظر حتي اتمام العملية الاولي  ');
                }
                $amount = round((float) $data['amount'], 2);
                if ((float) $user->balance + 0.001 < $amount) {
                    throw new \RuntimeException(' الرصيد المتاح للسحب اقل من المبلغ المطلوب  ');
                }
                WithDraw::create([
                    'user_id' => $user->id, 'amount' => $amount, 'method' => $data['method'], 'account_name' => $data['account_name'],
                    // Optional fields are saved as '' (older servers may have them NOT NULL).
                    'account_number' => $data['account_number'], 'bank_name' => (string) ($data['bank_name'] ?? ''), 'swift_code' => (string) ($data['swift_code'] ?? ''),
                    'iban_code' => (string) ($data['iban_code'] ?? ''), 'country' => (string) ($data['country'] ?? 'مصر'), 'status' => 0,
                ]);
                $user->update(['balance' => round((float) $user->balance - $amount, 2)]);
                \App\Models\WalletTransaction::create(['user_id' => $user->id, 'type' => 'withdraw', 'amount' => $amount, 'description' => 'طلب سحب ' . \App\Support\Money::format($amount) . ' (بانتظار التحويل)']);
            });
        } catch (\RuntimeException $e) {
            return Redirect::back()->withErrors($e->getMessage())->withInput();
        }

        return $this->success_message(' تم اضافة طلب السحب الخاص بك بنجاح  ');
    }
}
