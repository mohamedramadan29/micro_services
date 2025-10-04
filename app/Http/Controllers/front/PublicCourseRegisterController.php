<?php

namespace App\Http\Controllers\front;

use App\Http\Traits\Message_Trait;
use Illuminate\Http\Request;
use App\Models\admin\PublicCourse;
use App\Http\Controllers\Controller;
use App\Models\admin\PublicCourseRegister;
use Illuminate\Support\Facades\Validator;

class PublicCourseRegisterController extends Controller
{
    use Message_Trait;
    public function RegisterCourse(Request $request, $url)
    {
        $course = PublicCourse::where("url", $url)->first();
        if (!$course) {
            return to_route("home");
        }
        if ($request->isMethod("post")) {
            $data = $request->all();
            $rules = [
                "name" => "required",
                "phone" => "required",
                "certificate" => "required",
                "country" => "required",
                "city" => "required",
            ];
            $messages = [
                "name.required" => " من فضلك ادخل الاسم ",
                "phone.required" => " من فضلك ادخل رقم الهاتف ",
                "certificate.required" => " من فضلك ادخل المؤهل الدراسي ",
                "country.required" => " من فضلك ادخل الدولة ",
                "city.required" => " من فضلك ادخل المحافظة  ",
            ];
            $validator = Validator::make($data, $rules, $messages);
            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput();
            }
            $courseRegister = new PublicCourseRegister();
            $courseRegister->public_course_id = $course->id;
            $courseRegister->name = $data["name"];
            $courseRegister->phone = $data["phone"];
            $courseRegister->certificate = $data["certificate"];
            $courseRegister->country = $data["country"];
            $courseRegister->city = $data["city"];
            $courseRegister->note = $data["note"];
            $courseRegister->save();
            return $this->success_message(' تم التسجيل بنجاح في الكورس سوف نتواصل معك في اقرب وقت ممكن  ');
        }
        return view("website.landing-courses.index", compact("course"));
    }
}
