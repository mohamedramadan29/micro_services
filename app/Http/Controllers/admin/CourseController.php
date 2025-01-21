<?php

namespace App\Http\Controllers\admin;

use App\Models\User;
use App\Models\front\Course;
use Illuminate\Http\Request;
use App\Http\Traits\Message_Trait;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Notifications\AdminActiveCourse;
use Illuminate\Support\Facades\Notification;

class CourseController extends Controller
{
    use Message_Trait;
    public function index()
    {
        $courses = Course::with('User')->orderBy('created_at', 'desc')->get();

        return view('admin.courses.index', compact('courses'));
    }

    public function update_status(Request $request, $id)
    {
        DB::beginTransaction();
        $course = Course::find($id);
        $user = User::find($course->user_id);
        $course->status = 1;
        $course->save();
        ############# Send Notofication Mails And Db To Users
        Notification::send($user, new AdminActiveCourse($user->id, $course->id, $course->slug, $course->title));
        #####################################
        DB::commit();
        return $this->success_message(' تم تفعيل الكورس  وظهورة علي الموقع ');
    }

    public function update(Request $request, $id){
        $course = Course::find($id);
        if($request->isMethod('post')){
            $status = $request->get('status');
            $course->update([
                'status' => $status
            ]);
            return $this->success_message('تم تعديل حالة الكورس بنجاح');
        }
        return view('admin.courses.view',compact('course'));
    }

    public function delete($id){
        $course = Course::find($id);
        $course->delete();
        return  $this->success_message('تم حذف الكورس بنجاح ');
    }
}
