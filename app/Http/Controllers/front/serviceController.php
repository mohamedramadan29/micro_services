<?php

namespace App\Http\Controllers\front;

use App\Http\Controllers\Controller;
use App\Http\Traits\Message_Trait;
use App\Http\Traits\Slug_Trait;
use App\Http\Traits\Upload_Images;
use App\Models\admin\Category;
use App\Models\admin\Service;
use App\Models\admin\SubCategory;
use App\Models\front\TemporaryFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class serviceController extends Controller
{
    use Message_Trait;
    use Upload_Images;
    use Slug_Trait;

    public function index()
    {
        $services = Service::with('category')->where('user_id', Auth::id())->get();
        // dd($services);
        return view('website.service.index', compact('services'));
    }

    public function getSubCategories($category_id)
    {
        $subCategories = SubCategory::where('parent_id', $category_id)->get();
        return response()->json($subCategories);
    }

    public function add(Request $request)
    {
        try {
            if ($request->isMethod('post')) {
                $data = $request->all();
                $rules = [
                    'name' => 'required',
                    'cat_id' => 'required',
                    'description' => 'required|min:20',
                    'image'=>'required|image',
                    'price' => 'required|numeric'
                ];
                $messages = [
                    'name.required' => 'من فضلك ادخل اسم الخدمة ',
                    'cat_id.required' => 'من فضلك حدد القسم للخدمة  ',
                    'description.required' => 'من فضلك ادخل وصف الخدمة',
                    'image.required'=>' من فضلك ادخل الصورة  ',
                    'image.image'=>' من فضلك ادخل الصورة بشكل صحيح  ',
                    'description.min' => 'يجب ان يكون وصف الخدمة اكبر من 20 حرف ',
                    'price.required' => ' من فضلك ادخل سعر الخدمة  ',
                    'price.numeric' => ' يجب ان يكون سعر الخدمة رقم صحيح  '
                ];
                $validator = Validator::make($data, $rules, $messages);
                if ($validator->fails()) {
                    return Redirect::back()->withInput()->withErrors($validator);
                }

                if ($request->hasFile('image'))
                {
                    $filename = $this->saveImage($request->image,public_path('assets/uploads/services'));
                }

                $service = new Service();
                $service->create([
                    'name' => $data['name'],
                    'slug' => $this->CustomeSlug($data['name']),
                    'cat_id' => $data['cat_id'],
                    'sub_cat_id'=>$data['sub_cat_id'],
                    'user_id' => Auth::id(),
                    'image' => $filename,
                    'description' => $data['description'],
                    'price' => $data['price'],
                    'status' => 0,
                    'tags' => $data['tags'],
                ]);

                return $this->success_message('تم اضافة الخدمة بنجاح من فضلك انتظر التفعيل من الادارة');
            }
        } catch (\Exception $e) {
            return $this->exception_message($e);
        }
        $categories = Category::where('status', 1)->get();
        $subCategories = SubCategory::where('status', 1)->get();
        return view('website.service.add', compact('categories', 'subCategories'));
    }
//    public function tmpUpload(Request $request)
//    {
//        if ($request->hasFile('image')) {
//            $image = $request->file('image');
//            $file_name = $image->getClientOriginalName();
//            $folder = uniqid('serviceImage', true);
//
//            // التأكد من استخدام طريقة التخزين بشكل صحيح
//            $image->storeAs('public/assets/uploads/services/tmp/' . $folder, $file_name);
//
//            // إنشاء سجل في جدول TemporaryFile
//            $temp_file = new TemporaryFile();
//            $temp_file->create([
//                'folder' => $folder,
//                'file' => $file_name
//            ]);
//
//            return response()->json(['folder' => $folder, 'file' => $file_name], 200);
//        } else {
//            return response()->json(['error' => 'No file uploaded'], 400);
//        }
//    }

    public function update(Request $request, $id)
    {
        $service = Service::findOrFail($id);
        $user_serve = $service['user_id'];
        if ($user_serve != Auth::user()->id) {
            return Redirect::to('user/dashboard');
        }
        try {
            if ($request->isMethod('post')) {
                $data = $request->all();
                // dd($data);
                $rules = [
                    'name' => 'required',
                    'cat_id' => 'required',
                    'description' => 'required|min:20',
                    'price' => 'required|numeric'
                ];

                if ($request->hasFile('image')) {
                    $rules['image'] = 'required|image';
                }
                $messages = [
                    'name.required' => 'من فضلك ادخل اسم الخدمة ',
                    'cat_id.required' => 'من فضلك حدد القسم للخدمة  ',
                    'image.required' => 'من فضلك ادخل صورة الخدمة',
                    'description.required' => 'من فضلك ادخل وصف الخدمة',
                    'description.min' => 'يجب ان يكون وصف الخدمة اكبر من 20 حرف ',
                    'price.required' => ' من فضلك ادخل سعر الخدمة  ',
                    'price.numeric' => ' يجب ان يكون سعر الخدمة رقم صحيح  '

                ];
                $validator = Validator::make($data, $rules, $messages);
                if ($validator->fails()) {
                    return Redirect::back()->withInput()->withErrors($validator);
                }
                if ($request->hasFile('image')) {
                    $filename = $this->saveImage($request->image, public_path('assets/uploads/services'));
                    if ($service['image'] != '') {
                        unlink(public_path('assets/uploads/services/' . $service['image']));
                    }
                    $service->update([
                        'image' => $filename
                    ]);
                }
                $service->update([
                    'name' => $data['name'],
                    'slug' => $this->CustomeSlug($data['name']),
                    'cat_id' => $data['cat_id'],
                    'sub_cat_id'=>$data['sub_cat_id'],
                    'user_id' => Auth::id(),
                    'description' => $data['description'],
                    'price' => $data['price'],
                    'status' => 0,
                    'tags' => $data['tags'],
                ]);
                return $this->success_message('تم  تعديل الخدمة بنجاح من فضلك انتظر التفعيل من الادارة');
            }

        } catch (\Exception $e) {
            return $this->exception_message($e);
        }
        $categories = Category::where('status', 1)->get();
        $subCategories = SubCategory::where('status', 1)->get();
        return view('website.service.update', compact('categories','subCategories', 'service'));
    }

    public function delete($id)
    {
        try {
            $serivces = Service::findOrFail($id);
            if ($serivces['image'] != '') {
                unlink(public_path('assets/uploads/services/' . $serivces['image']));
            }
            $serivces->delete();
            return $this->success_message('تم حذف الخدمة بنجاح');
        } catch (\Exception $e) {
            return $this->exception_message($e);
        }
    }
}
