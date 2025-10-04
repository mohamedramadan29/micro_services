<?php

namespace App\Http\Controllers\admin;

use Illuminate\Http\Request;
use App\Http\Traits\Slug_Trait;
use App\Http\Traits\Message_Trait;
use App\Http\Traits\Upload_Images;
use App\Models\admin\PublicCourse;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;

class PublicCoursesPageController extends Controller
{
    use Message_Trait;
    use Slug_Trait;
    use Upload_Images;
    public function index()
    {
        $courses = PublicCourse::with('registers')->orderBy("created_at", "desc")->paginate(10);
        return view('admin.landing-courses.index', compact('courses'));
    }
    public function store(Request $request)
    {
        if ($request->isMethod('post')) {
            $data = $request->all();
            //  dd($data);
            $rules = [
                'name' => 'required|unique:public_courses,name',
                'image' => 'image|required|mimes:jpeg,png,jpg,gif,svg,webp',
                'description' => 'required',
                'meta_url' => 'required',
            ];
            $messages = [
                'name.required' => ' من فضلك ادخل الاسم  ',
                'image.required' => 'من فضلك ادخل صورة الدورة',
                'meta_url.required' => 'من فضلك ادخل رابط الدورة',
                'description.required' => 'من فضلك ادخل وصف الدورة',
                'name.unique' => 'اسم الدورة موجود بالفعل',
                'image.image' => 'من فضلك ادخل صورة بشكل صحيح',
                'image.mimes' => 'نوع الصورة يجب استخدامه jpeg,png,jpg,gif,svg,webp',
            ];
            $validator = Validator::make($data, $rules, $messages);
            if ($validator->fails()) {  // if there are errors
                return Redirect::back()->withInput()->withErrors($validator);
            }
            if ($request->hasFile('image')) {
                $filename = $this->saveImage($request->image, public_path('assets/uploads/public-courses'));
            }
            $course = new PublicCourse;
            $course->name = $data['name'];
            $course->slug = $this->CustomeSlug($data['name']);
            $course->description = $data['description'];
            $course->image = $filename;
            $course->url = $data['meta_url_final'];
            $course->save();
            return $this->success_message(' تم اضافة الكورس بنجاح  ');
        }
        return view('admin.landing-courses.store');
    }

    ############# Start Update



    public function update(Request $request, $id)
    {
        $course = PublicCourse::find($id);
        if (!$course) {
            abort(404);
        }
        if ($request->isMethod('post')) {
            $data = $request->all();
            //  dd($data);
            $rules = [
                'name' => 'required',
                'image' => 'image|nullable|mimes:jpeg,png,jpg,gif,svg,webp',
                'description' => 'required',
                'meta_url' => 'required',
            ];
            $messages = [
                'name.required' => ' من فضلك ادخل الاسم  ',

                'meta_url.required' => 'من فضلك ادخل رابط الدورة',
                'description.required' => 'من فضلك ادخل وصف الدورة',
                'image.image' => 'من فضلك ادخل صورة بشكل صحيح',
                'image.mimes' => 'نوع الصورة يجب استخدامه jpeg,png,jpg,gif,svg,webp',
            ];
            $validator = Validator::make($data, $rules, $messages);
            if ($validator->fails()) {
                return Redirect::back()->withInput()->withErrors($validator);
            }
            $course->update([
                'name' => $data['name'],
                'slug' => $this->CustomeSlug($data['name']),
                'description' => $data['description'],
                'url' => $data['meta_url_final'],
            ]);
            if ($request->hasFile('image')) {
                #### Delete Old Image
                $oldImg = public_path('assets/uploads/public-courses/' . $course->image);
                if (file_exists($oldImg) && isset($course->image)) {
                    @unlink($oldImg);
                }
                $filename = $this->saveImage($request->image, public_path('assets/uploads/public-courses'));
                $course->update([
                    'image' => $filename,
                ]);
            }
            return $this->success_message(' تم تعديل الكورس بنجاح  ');
        }
        return view('admin.landing-courses.update', compact('course'));
    }

    public function delete($id)
    {
        $course = PublicCourse::find($id);
        ### Delete Course Image
        $fileImage = public_path('assets/uploads/public-courses/' . $course->image);
        if (file_exists($fileImage)) {
            @unlink($fileImage);
        }
        if ($course->delete()) {
            return $this->success_message('تم حذف الكورس بنجاح ');
        }
    }

    public function registers($id)
    {
        $course = PublicCourse::find($id);
        $registers = $course->registers;
        return view('admin.landing-courses.registers', compact('registers'));
    }
}
