<?php

namespace App\Http\Controllers\front;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;

class AccountTypeController extends Controller
{
    public function edit()
    {
        if (filled(Auth::user()->account_type)) {
            return redirect()->intended('dashboard');
        }

        return view('website.user.account-type');
    }

    public function update(Request $request)
    {
        $user = Auth::user();
        if (filled($user->account_type)) {
            return redirect()->intended('dashboard');
        }

        $validator = Validator::make($request->all(), [
            'account_type' => 'required|in:' . User::TYPE_CLIENT . ',' . User::TYPE_FREELANCER,
            'whatsapp' => ['required_if:account_type,' . User::TYPE_FREELANCER, 'nullable', 'string', 'max:30', User::WHATSAPP_RULE],
        ], [
            'whatsapp.required_if' => 'رقم واتساب مطلوب لحسابات المستقلين',
            'whatsapp.regex' => 'رقم واتساب غير صحيح، اكتبه مع رمز الدولة',
            'account_type.required' => ' من فضلك اختر نوع الحساب: عميل او مستقل  ',
            'account_type.in' => ' نوع الحساب غير صحيح  ',
        ]);
        if ($validator->fails()) {
            return Redirect::back()->withErrors($validator)->withInput();
        }

        $user->update(['account_type' => $request->input('account_type')]);
        if ($user->isFreelancer()) {
            $user->forceFill(['whatsapp' => trim((string) $request->input('whatsapp')), 'freelancer_status' => User::FREELANCER_PENDING])->save();
        }

        if ($user->isFreelancer()) {
            return redirect()->route('specializations.edit')
                ->with('Success_message', 'اختر تخصصاتك لتظهر لك طلبات العملاء المناسبة');
        }

        return redirect()->intended('dashboard');
    }
}
