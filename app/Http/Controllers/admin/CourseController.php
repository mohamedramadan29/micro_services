<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Http\Traits\Message_Trait;
use App\Models\front\Course;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    use Message_Trait;
    public function index()
    {
        $courses = Course::with('User')->orderBy('created_at','desc')->get();

        return view('admin.courses.index',compact('courses'));
    }

    public function update_status(Request $request, $id)
    {
        $course = Course::find($id);
        $course->status = 1;
        $course->save();
        return $this->success_message(' تم تفعيل الكورس  وظهورة علي الموقع ');
    }
}
