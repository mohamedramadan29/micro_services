<?php

namespace App\Http\Controllers\front;

use App\Http\Controllers\Controller;
use App\Http\Traits\Message_Trait;
use App\Models\front\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use App\Http\Traits\Slug_Trait;
use App\Http\Traits\Upload_Images;

class CourseController extends Controller
{
    use Message_Trait;
    use Slug_Trait;
    use Upload_Images;

    public function index()
    {
        $courses = Course::where("status", 1)->orderBy("id", "desc")->paginate(10);
        return view("website.courses", compact("courses"));
    }
    public function course_details($id, $slug)
    {
        $course = Course::with('User')->where('id', $id)->where('slug', $slug)->first();
        //dd($course);
        if ($course) {
            return view('website.course_details', compact('course'));
        }
        abort(404);
    }

    /////////////////////// Start User Course Cruds ////////////////////////////

    public function user_courses()
    {
        $courses = Course::where('user_id', Auth::user()->id)->orderBy('id', 'desc')->paginate(10);
        return view('website.courses.index', compact('courses'));
    }
    public function store(Request $request)
    {

        if ($request->isMethod('post')) {
            try {
                $data = $request->all();
                $rules = [
                    'title' => 'required',
                    'desc' => 'required|min:50',
                    'price' => 'required',
                    'leason_numbers' => 'required|numeric',
                    'image' => 'required|image'
                ];
                $messages = [
                    'title.required' => ' من فضلك ادخل عنوان الكورس   ',
                    'desc.required' => ' من فضلك ادخل وصف كاملا للكورس   ',
                    'desc.min' => ' اقل وصف للكورس هو 50 حرف  ',
                    'price.required' => '  من فضلك ادخل سعر الكورس  ',
                    'leason_numbers.required' => '  من فضلك ادخل عدد المحاضرات ',
                    'image.required' => ' من فضلك ادخل صورة الكورس  ',
                    'image.image' => ' من فضلك ادخل صورة بشكل صحيح  ',
                ];
                $validator = Validator::make($data, $rules, $messages);
                if ($validator->fails()) {
                    return Redirect::back()->withInput()->withErrors($validator);
                }
                if ($request->hasFile('image')) {
                    $filename = $this->saveImage($request->image, public_path('assets/uploads/courses'));
                }

                DB::beginTransaction();
                $course = new Course();
                $course->user_id = Auth::id();
                $course->title = $data['title'];
                $course->slug = $this->CustomeSlug($data['title']);
                $course->image = $filename;
                $course->desc = $data['desc'];
                $course->adv = $data['adv'];
                $course->learn = $data['learn'];
                $course->price = $data['price'];
                $course->terms_course = $data['terms_course'];
                $course->course_hourse = $data['course_hourse'];
                $course->leason_numbers = $data['leason_numbers'];
                $course->student_num = $data['student_num'];
                // $course->start_date = $data['start_date'];
                // $course->end_date = $data['end_date'];
                $course->save();
                DB::commit();
                return $this->success_message(' تم اضافة الكورس  بنجاح من فضلك انتظر التفعيل من الادارة  ');
            } catch (\Exception $e) {
                return $this->exception_message($e);
            }
        }
        return view('website.courses.add');
    }




    public function update(Request $request, $id)
    {
        $course = Course::findOrFail($id);
        if ($course->user_id != Auth::id()) {
            abort(404);
        }

        if ($request->isMethod('post')) {
            try {
                $data = $request->all();
                $rules = [
                    'title' => 'required',
                    'desc' => 'required|min:50',
                    'price' => 'required',
                    'leason_numbers' => 'required|numeric',
                    // 'image'=>'required|image'
                ];
                if ($request->has('image')) {
                    $rules['image'] = 'required|image';
                }
                $messages = [
                    'title.required' => ' من فضلك ادخل عنوان الكورس   ',
                    'desc.required' => ' من فضلك ادخل وصف كاملا للكورس   ',
                    'desc.min' => ' اقل وصف للكورس هو 50 حرف  ',
                    'price.required' => '  من فضلك ادخل سعر الكورس  ',
                    'leason_numbers.required' => '  من فضلك ادخل عدد المحاضرات ',
                    'image.required' => ' من فضلك ادخل صورة الكورس  ',
                    'image.image' => ' من فضلك ادخل صورة بشكل صحيح  ',
                ];
                $validator = Validator::make($data, $rules, $messages);
                if ($validator->fails()) {
                    return Redirect::back()->withInput()->withErrors($validator);
                }
                DB::beginTransaction();
                if ($request->hasFile('image')) {
                    ////////// Delete Old Images

                    $old_image = public_path('assets/uploads/courses/' . $course['image']);
                    if (file_exists($old_image)) {
                        @unlink($old_image);
                    }
                    $filename = $this->saveImage($request->image, public_path('assets/uploads/courses'));

                    ////// Update Image

                    $course->update([
                        'image' => $filename,
                    ]);
                }
                $course->update([
                    "title" => $data['title'],
                    "slug" => $this->CustomeSlug($data['title']),
                    "desc" => $data['desc'],
                    "adv" => $data['adv'],
                    "learn" => $data['learn'],
                    "price" => $data['price'],
                    "terms_course" => $data['terms_course'],
                    "course_hourse" => $data['course_hourse'],
                    "leason_numbers" => $data['leason_numbers'],
                    "student_num" => $data['student_num'],
                ]);
                DB::commit();
                return $this->success_message(' تم تعديل الكورس  بنجاح من فضلك انتظر التفعيل من الادارة  ');
            } catch (\Exception $e) {
                return $this->exception_message($e);
            }
        }
        return view('website.courses.edit', compact('course'));
    }

    public function subscriptions($id){
        $course = Course::findOrFail($id);
        if($course->user_id != Auth::id()){
            abort(404);
        }
        $subscriptions = $course->Subscriptions()->orderBy('id','desc')->get();
        return view('website.courses.subscriptions',compact('subscriptions'));
    }
}
