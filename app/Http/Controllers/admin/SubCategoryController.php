<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Http\Traits\Message_Trait;
use App\Http\Traits\Slug_Trait;
use App\Http\Traits\Upload_Images;
use App\Models\admin\Category;
use App\Models\admin\SubCategory;
use Illuminate\Http\Request;

class SubCategoryController extends Controller
{

    use Message_Trait;
    use Upload_Images;
    use Slug_Trait;

    public function index()
    {
        $subcategories = SubCategory::with('category')->get();
        $categories = Category::all();
        return view('admin.sub-categories.index', compact('subcategories','categories'));
    }

    public function store(Request $request)
    {
        try {
            $data = $request->all();
            $rules = [];
            $messages = [];
            $this->validate($request, $rules, $messages);
            $subcategory = new SubCategory();
            if ($request->hasFile('image')) {
                $filename = $this->saveImage($request->image, public_path('assets/uploads/service_category'));
            }
            $subcategory->create([
                'name' => $data['name'],
                'slug' => $this->CustomeSlug($data['name']),
                'image' => $filename,
                'parent_id' => $data['parent_id'],
                'status' => $data['status'],
            ]);
            return $this->success_message('تم اضافة القسم الفرعي بنجاح ');

        } catch (\Exception $e) {
            return $this->exception_message($e);
        }
    }

    public function update(Request $request, $id)
    {
        $subCategory = SubCategory::findOrFail($id);
        try {
            $data = $request->all();
            $rules = [];
            $messages = [];
            $this->validate($request, $rules, $messages);

            if ($request->hasFile('image')) {
                $filename = $this->saveImage($request->image, public_path('assets/uploads/service_category'));
                $old_image = public_path('assets/uploads/service_category/' . $subCategory->image);
                if (file_exists($old_image) && isset($subCategory->image)) {
                    unlink($old_image);
                }
                $subCategory->update([
                    'image' => $filename
                ]);
            }


            $subCategory->update([
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
            $subCategory = SubCategory::findOrFail($id);
            $old_image = public_path('assets/uploads/service_category/' . $subCategory->image);
            if (file_exists($old_image) && isset($subCategory->image)) {
                unlink($old_image);
            }
            $subCategory->delete();
            return $this->success_message('تم حذف القسم بنجاح ');
        } catch (\Exception $e) {
            return $this->exception_message($e);
        }

    }

}