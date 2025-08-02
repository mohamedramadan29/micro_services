<?php

namespace App\Http\Controllers\admin;

use Illuminate\Http\Request;
use App\Http\Traits\Slug_Trait;
use App\Http\Traits\Message_Trait;
use App\Http\Traits\Upload_Images;
use App\Models\admin\BlogCategory;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;

class BlogCategoryController extends Controller
{
    use Message_Trait;
    use Upload_Images;
    use Slug_Trait;

    public function index()
    {
        $categories = BlogCategory::orderBy('id','desc')->get();
        return view('admin.BlogCategory.index', compact('categories'));
    }


    public function store(Request $request)
    {
        if ($request->isMethod('post')) {
            try {
                $data = $request->all();
                $rules = [
                    'name' => 'required',
                    'description' => 'required',
                    'image'=>'required|image',
                    'status'=>'required',
                ];
                $messages = [
                    'name.required' => ' من فضلك ادخل الاسم   ',
                    'description.required' => ' من فضلك ادخل الوصف    ',
                    'image.required'=> ' من فضلك ادخل صورة القسم  ',
                    'status.required'=> ' من فضلك ادخل حالة القسم  ',

                ];
                $validator = Validator::make($data, $rules, $messages);
                if ($validator->fails()) {
                    return Redirect::back()->withInput()->withErrors($validator);
                }


                if ($request->hasFile('image')) {
                    $file_name = $this->saveImage($request->image, public_path('assets/uploads/BlogCategory'));
                }
                $category = new BlogCategory();
                $category->create([
                    'name' => $data['name'],
                    'slug' => $this->CustomeSlug($data['name']),
                    'description' => $data['description'],
                    'image'=>$file_name,
                    'status'=>$data['status'],
                    'meta_title'=>$data['meta_title'],
                    'meta_url'=>$data['meta_url_final'],
                    'meta_description'=>$data['meta_description'],
                    'meta_keywords'=>$data['meta_keywords'],
                ]);
                return $this->success_message(' تم اضافة القسم  بنجاح  ');
            } catch (\Exception $e) {
                return $this->exception_message($e);
            }
        }
        return view('admin.BlogCategory.add');
    }

    public function update(Request $request, $id)
    {
        $category = BlogCategory::findOrFail($id);
        if ($request->isMethod('post')) {
            try {
                $data = $request->all();
                $rules = [
                    'name' => 'required',
                    'description' => 'required',
                    'status'=>'required',
                ];
                if ($request->hasFile('image')) {
                    $rules['image'] = 'image|mimes:jpeg,png,jpg,gif|max:2048'; // حدد أنواع الصور والحجم الأقصى (2MB)
                }
                $messages = [
                    'name.required' => 'من فضلك ادخل العنوان',
                    'description.required' => 'من فضلك ادخل الوصف',
                    'status.required' => 'من فضلك ادخل حالة القسم',
                    'image.image' => 'من فضلك ادخل صورة صالحة',
                    'image.mimes' => 'يجب أن تكون الصورة من نوع jpeg, png, jpg, أو gif',
                    'image.max' => 'يجب ألا يتجاوز حجم الصورة 2 ميجابايت',
                ];
                $validator = Validator::make($data, $rules, $messages);
                if ($validator->fails()) {
                    return Redirect::back()->withInput()->withErrors($validator);
                }

                if ($request->hasFile('image')) {
                    $file_name = $this->saveImage($request->image, public_path('assets/uploads/BlogCategory'));

                    ////// / Delete Old Image

                    $old_image = public_path('assets/uploads/BlogCategory/'.$category['image']);
                    if (file_exists($old_image)){
                        @unlink($old_image);
                    }
                    $category->update([
                        'image'=>$file_name,
                    ]);
                }


                $category->update([
                    'name' => $data['name'],
                    'slug' => $this->CustomeSlug($data['name']),
                    'description' => $data['description'],
                    'status'=>$data['status'],
                    'meta_title'=>$data['meta_title'],
                    'meta_url'=>$data['meta_url_final'],
                    'meta_description'=>$data['meta_description'],
                    'meta_keywords'=>$data['meta_keywords'],
                ]);
                return $this->success_message(' تم تعديل القسم  بنجاح  ');
            } catch (\Exception $e) {
                return $this->exception_message($e);
            }
        }
        return view('admin.BlogCategory.update', compact('category'));
    }

    public function delete($id)
    {
        $category = BlogCategory::findOrFail($id);
        $old_image = public_path('assets/uploads/BlogCategory/'.$category['image']);
        if (file_exists($old_image)){
            @unlink($old_image);
        }
        $category->delete();
        return $this->success_message(' تم حذف القسم ');
    }
}
