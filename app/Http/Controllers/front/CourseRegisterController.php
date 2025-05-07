<?php

namespace App\Http\Controllers\front;

use Stripe\Stripe;
use App\Models\User;
use App\Models\front\Course;
use Illuminate\Http\Request;
use App\Models\admin\Setting;
use App\Http\Traits\Message_Trait;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use App\Http\Controllers\Controller;
use App\Models\front\CourseRegister;
use Illuminate\Support\Facades\Auth;
use App\Notifications\NewCourseRegister;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Notification;
use Stripe\Checkout\Session as StripeSession;

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
        $user = User::where('id', Auth::user()->id)->first();
        // إنشاء جلسة دفع في Stripe
        Stripe::setApiKey(env('STRIPE_SECRET'));
        $checkoutSession = StripeSession::create([
            'payment_method_types' => ['card', 'alipay'],
            'line_items' => [
                [
                    'price_data' => [
                        'currency' => 'usd',
                        'product_data' => [
                            'name' => $course->title,
                        ],
                        'unit_amount' => $course->price * 100, // تحويل المبلغ إلى سنت
                    ],
                    'quantity' => 1,

                ]
            ],
            'mode' => 'payment',
            'success_url' => URL::route('course.payment.success', ['course_id' => $course->id, 'user_id' => $user->id]),
            'cancel_url' => URL::route('course.payment.cancel'),
        ]);
        return redirect($checkoutSession->url);
        // if ($course) {
        //     $user = User::where('id', Auth::user()->id)->first();
        //     $user_balance = $user->balance;
        //     if ($user_balance < $course->price) {
        //         //return Redirect::back()->withErrors(['  رصيدك الحالي لا يكفي للاشتراك في الكورس من فضلك اشحن الرصيد الخاص بك  ']);
        //         return Redirect()->route('user_balance')->withErrors([
        //             'رصيدك الحالي غير كافٍ للاشتراك في الكورس. يرجى شحن رصيدك في الموقع أولاً لإتمام الاشتراك.'
        //         ]);
        //     }
        //     try {

        //         DB::beginTransaction();
        //         //////////  course Register
        //         $register = new CourseRegister();
        //         $register->user_id = Auth::user()->id;
        //         $register->course_id = $course->id;
        //         $register->price = $course->price;
        //         $register->title = $course->title;
        //         $register->slug = $course->slug;
        //         $register->course_owner_profit = $course_owner_profit;
        //         $register->website_profit = $website_commission_profit;
        //         $register->save();

        //         //////////// Increase Course Owner Balance

        //         $owner = User::where('id', $course->user_id)->first();
        //         $owner->balance += $course_owner_profit;
        //         $owner->save();

        //         /////////// DeCrease Register User Balance

        //         $user_balance = $user->balance - $course->price;

        //         $user->balance = $user_balance;
        //         $user->save();

        //         ///// Update Course

        //         $course->current_student_num++;
        //         $course->save();
        //         ################# Update Website Balance
        //         $public_setting = Setting::first();
        //         $website_balance = $public_setting->website_balance + $website_commission_profit;

        //         $public_setting->website_balance = $website_balance;
        //         $public_setting->save();
        //         #################
        //         ######### Send Notification To Course Owner
        //         Notification::send($owner, new NewCourseRegister($user->id, $course->id, $course->slug, $course->title));
        //         DB::commit();

        //         return $this->success_message(' تم الاشتراك في الكورس بنجاح  ');
        //     } catch (\Exception $e) {
        //         return $this->exception_message($e);
        //     }
        // }

        //  abort(404);
    }


    public function payment_success(Request $request)
    {
        $course = Course::findOrFail($request->course_id);
        $user = User::findOrFail($request->user_id);

        DB::beginTransaction();
        //try {
        // تسجيل الاشتراك
        $register = new CourseRegister();
        $register->user_id = $user->id;
        $register->course_id = $course->id;
        $register->price = $course->price;
        $register->title = $course->title;
        $register->slug = $course->slug;
        $register->course_owner_profit = $course->price * (1 - (floatval(Setting::first()->website_commission) / 100));
        $register->website_profit = $course->price * (floatval(Setting::first()->website_commission) / 100);
        $register->save();

        // إضافة الربح إلى رصيد صاحب الكورس
        $owner = User::findOrFail($course->user_id);
        $owner->balance += $register->course_owner_profit;
        $owner->save();

        // تحديث عدد الطلاب المسجلين
        $course->current_student_num++;
        $course->save();

        // تحديث رصيد الموقع
        $public_setting = Setting::first();
        $public_setting->website_balance += $register->website_profit;
        $public_setting->save();

        // إرسال إشعار إلى مالك الكورس
        //   Notification::send($owner, new NewCourseRegister($user->id, $course->id, $course->slug, $course->title));

        DB::commit();

        // return redirect()->route('user_dashboard')->with('success', 'تم الاشتراك في الكورس بنجاح.');
        return Redirect()->route('course_details',[$course->id,$course->slug])->with('success_message', 'تم الاشتراك في الكورس بنجاح.');
        //return $this->success_message(' تم الاشتراك في الكورس بنجاح  ');
        // } catch (\Exception $e) {
        //     DB::rollBack();
        //     return $this->Error_message('حدث خطاء في معالجة الدفع.');
        //     // return redirect()->route('user_dashboard')->with('error', 'حدث خطأ أثناء معالجة الدفع.');
        // }
    }


    public function payment_cancel()
    {
        return $this->Error_message('تم الغاء الدفع');
    }
}
