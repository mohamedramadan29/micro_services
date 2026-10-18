<?php

namespace App\Http\Controllers\front;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\EmailVerification;
use Illuminate\Http\Request;

/** Confirming the account email (also verifies freelancers, client decision 2026-10-16). */
class EmailVerificationController extends Controller
{
    public function verify(Request $request, User $user, string $hash)
    {
        if (! EmailVerification::confirm($user, $hash)) {
            return redirect('login')->withErrors('رابط التأكيد غير صالح. اطلب رابطًا جديدًا.');
        }

        return redirect(auth()->id() === $user->id ? route('dashboard') : url('login'))
            ->with('Success_message', 'تم تأكيد بريدك الإلكتروني، شكرًا لك.');
    }

    /** A new link: for the logged-in user, or by email (same answer whether the email exists or not). */
    public function resend(Request $request)
    {
        $user = $request->user();
        if (! $user) {
            $email = $request->validate(['email' => 'required|email|max:255'], [
                'email.required' => 'اكتب بريدك الإلكتروني',
                'email.email' => 'البريد الإلكتروني غير صحيح',
            ])['email'];
            $user = User::where('email', $email)->first();
        }
        if ($user && ! $user->email_verified_at) {
            EmailVerification::send($user);
        }

        return back()->with('Success_message', 'إن كان البريد مسجلًا ولم يُؤكَّد بعد، فقد أرسلنا إليه رابط تأكيد جديدًا صالحًا لمدة ' . EmailVerification::VALID_DAYS . ' أيام.');
    }
}
