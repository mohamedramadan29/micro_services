<?php

namespace App\Http\Controllers\front;

use App\Http\Controllers\Controller;
use App\Http\Traits\Message_Trait;
use App\Models\front\Course;
use App\Models\front\CourseRegister;
use App\Models\OnlinePayment;
use App\Services\Payments\OnlinePaymentService;
use App\Services\Payments\RequestPaymentService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;

class CourseRegisterController extends Controller
{
    use Message_Trait;

    /** Pay the course price at EasyKash; the registration happens once EasyKash confirms it. */
    public function course_register($id, OnlinePaymentService $payments)
    {
        $course = Course::findOrFail($id);
        if (CourseRegister::where('user_id', Auth::id())->where('course_id', $course->id)->exists()) {
            return Redirect::back()->withErrors(['error' => 'أنت مشترك في هذا الكورس بالفعل']);
        }

        $slug = $course->slug ?: 'course';

        try {
            return redirect()->away($payments->start(OnlinePayment::PURPOSE_COURSE, Auth::user(), (float) $course->price, 'الاشتراك في كورس ' . $course->title, [
                'course_id' => $course->id,
                'owner_id' => $course->user_id,
                'title' => $course->title,
                'slug' => $slug,
                'commission_rate' => RequestPaymentService::commissionRate(),
                'back_url' => route('course_details', [$course->id, $slug]),
            ]));
        } catch (\Throwable $e) {
            return $this->payment_error($e);
        }
    }
}
