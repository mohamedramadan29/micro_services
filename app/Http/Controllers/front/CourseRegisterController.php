<?php

namespace App\Http\Controllers\front;

use App\Models\User;
use App\Models\front\Course;
use Illuminate\Http\Request;
use App\Http\Traits\Message_Trait;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\admin\Setting;
use App\Models\front\CourseRegister;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;

class CourseRegisterController extends Controller
{
    use Message_Trait;

    public function course_register(Request $request, $id)
    {

        $public_setting = Setting::first();
        $website_commission = floatval($public_setting['website_commission']);

        $course = Course::findOrFail($id);
        $website_commission_profit = ($course->price * $website_commission) / 100;
        $course_owner_profit = $course->price - $website_commission_profit;
        if ($course) {
            $user = User::where('id', Auth::user()->id)->first();
            $user_balance = $user->balance;
            if ($user_balance < $course->price) {
                return Redirect::back()->withErrors([' رصيدك الحالي لا يكفي للاشتراك في الكورس  ']);
            }
            try {

                DB::beginTransaction();
                //////////  course Register
                $register = new CourseRegister();
                $register->user_id = Auth::user()->id;
                $register->course_id = $course->id;
                $register->price = $course->price;
                $register->title = $course->title;
                $register->slug = $course->slug;
                $register->course_owner_profit = $course_owner_profit;
                $register->website_profit = $website_commission_profit;
                $register->save();

                //////////// Increase Course Owner Balance

                $owner = User::where('id', $course->user_id)->first();
                $owner->balance += $course_owner_profit;
                $owner->save();

                /////////// DeCrease Register User Balance

                $user_balance = $user->balance - $course->price;

                $user->balance = $user_balance;
                $user->save();

                ///// Update Course

                $course->current_student_num++;
                $course->save();

                DB::commit();

                return $this->success_message(' تم الاشتراك في الكورس بنجاح  ');
            } catch (\Exception $e) {
                return $this->exception_message($e);
            }


        }

        abort(404);
    }
}
