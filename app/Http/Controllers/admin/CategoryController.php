<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Http\Traits\Message_Trait;
use App\Http\Traits\Slug_Trait;
use App\Http\Traits\Upload_Images;
use App\Models\admin\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    use Message_Trait;
    use Upload_Images;
    use Slug_Trait;

    public function index()
    {
        $categories = Category::all();
        return view('admin.categories.index', compact('categories'));
    }

    public function store(Request $request)
    {
        try {
            $data = $request->all();
            $rules = [];
            $messages = [];
            $this->validate($request, $rules, $messages);
            $category = new Category();
            if ($request->hasFile('image')) {
                $filename = $this->saveImage($request->image, public_path('assets/uploads/service_category'));
            }
            $category->create([
                'name' => $data['name'],
                'slug' => $this->CustomeSlug($data['name']),
                'image' => $filename,
                'parent_id' => $data['parent_id'],
                'status' => $data['status'],
            ]);
            return $this->success_message('تم اضافة القسم بنجاح ');

        } catch (\Exception $e) {
            return $this->exception_message($e);
        }
    }

    public function update(Request $request, $id)
    {
        $category = Category::findOrFail($id);
        try {
            $data = $request->all();
            $rules = [];
            $messages = [];
            $this->validate($request, $rules, $messages);
            if ($request->hasFile('image')) {
                $filename = $this->saveImage($request->image, public_path('assets/uploads/service_category'));
                if ($category['image'] != '') {
                    unlink(public_path('assets/uploads/service_category/' . $category['image']));
                }
                $category->update([
                    'image' => $filename
                ]);
            }
            $category->update([
                'name' => $data['name'],
                'slug' => $this->CustomeSlug($data['name']),
                'parent_id' => $data['parent_id'],
                'status' => $data['status'],
            ]);
            return $this->success_message(' تم تعديل القسم بنجاح  ');

        } catch (\Exception $e) {
            return $this->exception_message($e);
        }
    }

    public function destroy($id)
    {
        try {
            $category = Category::findOrFail($id);
            if ($category['image'] != '') {
                unlink(public_path('assets/uploads/service_category/' . $category['image']));
            }
            $category->delete();
            return $this->success_message('تم حذف القسم بنجاح ');
        } catch (\Exception $e) {
            return $this->exception_message($e);
        }

    }
}
