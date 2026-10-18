<?php

namespace App\Http\Controllers\front;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\RequestUpdateNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;

/** «ملفي كمستقل»: what the admin reviews and the directory shows (client file «تعديلات», item 03). */
class FreelancerProfileController extends Controller
{
    public function edit()
    {
        $user = Auth::user();
        if (! $user->isFreelancer()) {
            return redirect('dashboard')->withErrors('هذه الصفحة لحسابات المستقلين فقط');
        }

        return view('website.user.freelancer-profile', [
            'user' => $user,
            'specializations' => $user->specializations()->orderBy('name')->pluck('name'),
        ]);
    }

    public function update(Request $request)
    {
        $user = Auth::user();
        if (! $user->isFreelancer()) {
            return redirect('dashboard')->withErrors('هذه الصفحة لحسابات المستقلين فقط');
        }

        $data = $request->validate([
            'whatsapp' => ['required', 'string', 'max:30', User::WHATSAPP_RULE],
            'bio' => 'required|string|min:20|max:500',
            'availability' => 'required|in:' . implode(',', array_keys(User::AVAILABILITY)),
            'languages' => 'nullable|string|max:255',
            'starting_price' => 'nullable|numeric|min:0|max:1000000',
        ], [
            'whatsapp.required' => 'رقم واتساب مطلوب',
            'whatsapp.regex' => 'رقم واتساب غير صحيح، اكتبه مع رمز الدولة',
            'bio.required' => 'اكتب نبذة قصيرة عنك',
            'bio.min' => 'النبذة قصيرة جدًا (20 حرفًا على الأقل)',
            'availability.required' => 'اختر حالة التوفر',
        ]);

        $user->fill($data);
        $resubmitted = $user->freelancer_status === User::FREELANCER_REJECTED || ! $user->freelancer_status;
        if ($resubmitted) {
            $user->freelancer_status = User::FREELANCER_PENDING;
        }
        $user->save();

        if ($resubmitted) {
            Notification::send(User::activeAdmins()->get(), new RequestUpdateNotification(
                \App\Support\Msg::t('admin.freelancer_pending_review', ['name' => $user->name]),
                '/admin/freelancers/' . $user->id,
                'admin_alerts'
            ));
        }

        $user->fresh()->markFreelancerProfileCompleted();

        return back()->with('Success_message', 'تم حفظ ملفك.');
    }
}
