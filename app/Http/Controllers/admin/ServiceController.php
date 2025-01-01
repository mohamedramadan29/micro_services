<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Http\Traits\Message_Trait;
use App\Http\Traits\Slug_Trait;
use App\Http\Traits\Upload_Images;
use App\Models\admin\Category;
use App\Models\admin\Service;
use App\Models\admin\SubCategory;
use App\Models\User;
use App\Notifications\AcceptJobFromAdmin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;

class ServiceController extends Controller
{
    use Message_Trait;
    use Slug_Trait;
    use Upload_Images;

    public function index()
    {
        $services = Service::with('category', 'subcategory')->orderBy('id', 'desc')->get();
        return view('admin.services.index', compact('services'));
    }

    public function store(Request $request)
    {
        $categories = Category::where('status', '1')->get();
        $subCategories = SubCategory::where('status', '1')->get();
        try {
            if ($request->isMethod('post')) {
                $data = $request->all();
                $rules = [
                    'name' => 'required',
                    'cat_id' => 'required',
                    'user_id' => 'required',
                    'image' => 'required|image',
                    'description' => 'required',
                    'price' => 'required'
                ];
                $messages = [
                    'name.required' => 'من فضلك ادخل اسم الخدمة ',
                    'cat_id.required' => 'من فضلك حدد القسم '
                ];
                $validator = Validator::make($data, $rules, $messages);
                if ($validator->fails()) {
                    return Redirect::back()->withInput()->withErrors($validator);
                }
                $service = new Service();
                if ($request->hasFile('image')) {
                    $filename = $this->saveImage($request->image, public_path('assets/uploads/services'));
                }
                $service->create([
                    'name' => $data['name'],
                    'slug' => $this->CustomeSlug($data['name']),
                    'cat_id' => $data['cat_id'],
                    'sub_cat_id' => $data['sub_cat_id'],
                    'user_id' => Auth::id(),
                    'image' => $filename,
                    'description' => $data['description'],
                    'price' => $data['price'],
                    'status' => $data['status'],
                    'tags' => $data['tags'],
                ]);
                return $this->success_message('تم اضافة خدمة جديدة بنجاح');
            }
        } catch (\Exception $e) {
            return $this->exception_message($e);
        }
        return view('admin.services.add', compact('categories', 'subCategories'));

    }

    public function update(Request $request, $id)
    {
        $categories = Category::where('status', '1')->get();
        $subCategories = SubCategory::where('status', '1')->get();
        $service = Service::findOrFail($id);
       // $user = User::where('id',$service['user_id'])->get();
        $user = User::find($service['user_id']);
        try {
            if ($request->isMethod('post')) {
                $data = $request->all();
                $rules = [
                    'name' => 'required', 'cat_id' => 'required',
                    'description' => 'required', 'price' => 'required'
                ];
                if ($request->hasFile('image')) {
                    $rules['image'] = 'image|required';
                }
                $messages = [
                    'name.required' => 'من فضلك ادخل اسم الخدمة ',
                    'cat_id.required' => 'من فضلك حدد القسم '
                ];
                $validator = Validator::make($data, $rules, $messages);
                if ($validator->fails()) {
                    return Redirect::back()->withInput()->withErrors($validator);
                }
                if ($request->hasFile('image')) {
                    $filename = $this->saveImage($request->image, public_path('assets/uploads/services'));
                    if ($service['image'] != '') {
                        unlink(public_path('assets/uploads/services/' . $service['image']));
                        $service->update([
                            'image' => $filename
                        ]);
                    }
                }
                $service->update([
                    'name' => $data['name'],
                    'slug' => $this->CustomeSlug($data['name']),
                    'cat_id' => $data['cat_id'],
                    'sub_cat_id' => $data['sub_cat_id'],
                    'description' => $data['description'],
                    'price' => $data['price'],
                    'tags' => $data['tags'],
                ]);
                if ($service->status == 0) {
                    if ($data['status'] == 1) {
                        //// Update Db
                        /// And Send Notification
                        $service->update([
                            'status' => $data['status'],
                        ]);
                        Notification::send($user, new AcceptJobFromAdmin($service['user_id'], $id, $this->CustomeSlug($data['name']), $data['name']));
                    }
                } else {
                    $service->update([
                        'status' => $data['status'],
                    ]);
                }
                return $this->success_message('تم تعديل  الخدمة بنجاح ');
            }
        } catch (\Exception $e) {
            return $this->exception_message($e);
        }
        return view('admin.services.edit', compact('service', 'categories', 'subCategories'));
    }


    public function getSubCategories($category_id)
    {
        $subCategories = SubCategory::where('parent_id', $category_id)->get();
        return response()->json($subCategories);
    }

    public function destroy($id)
    {
        try {
            $service = Service::findOrFail($id);
            if ($service['image'] != '') {
                @unlink(public_path('assets/uploads/services/' . $service['image']));
            }
            $service->delete();
            return $this->success_message('تم حذف الخدمة بنجاح ');
        } catch (\Exception $e) {
            return $this->exception_message($e);
        }

    }
}
