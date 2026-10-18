<?php

namespace App\Services\Payments\Purposes;

use App\Models\admin\Setting;
use App\Models\front\Course;
use App\Models\front\CourseRegister;
use App\Models\OnlinePayment;
use App\Models\User;

/** Registering in a paid course: the owner gets the price minus the site commission. */
class CoursePurpose extends SitePurpose
{
    public function paid(OnlinePayment $payment): void
    {
        $course = Course::find($payment->data('course_id'));
        $price = (float) $payment->amount;
        $websiteProfit = round($price * (float) $payment->data('commission_rate') / 100, 2);

        $register = new CourseRegister();
        $register->user_id = $payment->user_id;
        $register->course_id = $payment->data('course_id');
        $register->price = $price;
        $register->title = $course?->title ?? $payment->data('title');
        $register->slug = $course?->slug ?? $payment->data('slug');
        $register->course_owner_profit = $price - $websiteProfit;
        $register->website_profit = $websiteProfit;
        $register->save();

        if ($owner = User::whereKey($payment->data('owner_id'))->lockForUpdate()->first()) {
            $owner->balance = round((float) $owner->balance + $register->course_owner_profit, 2);
            $owner->save();
        }

        $course?->increment('current_student_num');

        if ($setting = Setting::query()->lockForUpdate()->first()) {
            $setting->website_balance = round((float) $setting->website_balance + $websiteProfit, 2);
            $setting->save();
        }
    }

    protected function doneUrl(OnlinePayment $payment): string
    {
        return route('course_details', [$payment->data('course_id'), $payment->data('slug')]);
    }

    protected function paidMessage(): string
    {
        return 'تم الاشتراك في الكورس بنجاح.';
    }
}
