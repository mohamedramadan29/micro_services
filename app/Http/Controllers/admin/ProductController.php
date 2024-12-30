<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Http\Traits\Message_Trait;
use App\Http\Traits\Slug_Trait;
use App\Http\Traits\Upload_Images;
use App\Models\admin\Product;
use App\Models\admin\productGallary;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    use Message_Trait;
    use Slug_Trait;
    use Upload_Images;

    public function index()
    {
        $products = Product::orderBy('id', 'desc')->get();
        return view('admin.Products.index', compact('products'));
    }


    public function store(Request $request)
    {
        if ($request->isMethod('post')) {
            try {
                $data = $request->all();
                // dd($data);
                $rules = [
                    'name' => 'required',

                    'description' => 'required'
                ];
                if ($request->hasFile('image')) {
                    $rules['image'] = 'image|mimes:jpg,png,jpeg,webp';
                }
                $messages = [
                    'name.required' => ' من فضلك ادخل اسم المنتج  ',

                    'description.required' => ' من فضلك ادخل الوصف الخاص بالمنتج ',
                    'image.mimes' =>
                        'من فضلك يجب ان يكون نوع الصورة jpg,png,jpeg,webp',
                    'image.image' => 'من فضلك ادخل الصورة بشكل صحيح',
                ];
                $validator = Validator::make($data, $rules, $messages);
                if ($validator->fails()) {
                    return Redirect::back()->withInput()->withErrors($validator);
                }
                if ($request->hasFile('image')) {
                    $file_name = $this->saveImage($request->image, public_path('assets/uploads/product_images'));
                }
                /////// Check if This Product In Db Or Not
                ///
                $count_old_product = Product::where('name', $data['name'])->count();
                if ($count_old_product > 0) {
                    return Redirect::back()->withInput()->withErrors(' اسم المنتج متواجد من قبل من فضلك ادخل منتج جديد  ');
                }
                DB::beginTransaction();
                $product = new Product();
                $product->name = $data['name'];
                if ($data['slug'] && $data['slug'] != '') {
                    $slug = $this->CustomeSlug($data['slug']);
                } else {
                    $slug = $this->CustomeSlug($data['name']);
                }
                $product->slug = $slug;
                $product->status = $data['status'];
                $product->short_description = $data['short_description'];
                $product->description = $data['description'];
                $product->price = $data['price'];
                $product->meta_title = $data['meta_title'];
                $product->meta_keywords = $data['meta_keywords'];
                $product->meta_description = $data['meta_description'];
                $product->image = $file_name;
                $product->save();
                ///////// Check If Product Gallary Not Empty
                ///
                if ($request->hasFile('gallery')) {
                    foreach ($request->file('gallery') as $gallery) { // تعديل `hasFile` إلى `file`
                        $gallery_name = $this->saveImage($gallery, 'assets/uploads/product_gallery');
                        $gallery_image = new productGallary(); // تأكد من استخدام اسم النموذج الصحيح
                        $gallery_image->product_id = $product->id;
                        $gallery_image->image = $gallery_name;
                        $gallery_image->save();
                    }
                }

                DB::commit();
                return $this->success_message(' تم اضافة المنتج بنجاح  ');
            } catch (\Exception $e) {
                return $this->exception_message($e);
            }
        }
        return view('admin.Products.add');

    }

    public function update(Request $request, $slug)
    {
        // جلب المنتج مع الفئة الفرعية والمتغيرات المرتبطة به
        $product = Product::where('slug', $slug)->first();
        $gallaries = ProductGallary::where('product_id', $product->id)->get();

        if ($request->isMethod('post')) {
            // التحقق من صحة المدخلات
            $request->validate([
                'name' => 'required|string|max:255',

            ]);
            DB::beginTransaction();

            try {
                $data = $request->all();
                //dd($data);
                // البحث عن المنتج للتعديل
                // $product = Product::find();  // استبدال $productId بالمعرّف الخاص بالمنتج

                if ($request->hasFile('image')) {
                    $old_image = public_path('assets/uploads/product_images'.$product['image']);
                    if(file_exists($old_image)){
                        unlink($old_image);
                    }
                    $file_name = $this->saveImage($request->image, public_path('assets/uploads/product_images'));
                    $product->update([
                        'image'=>$file_name,
                    ]);
                }
                // تحديث معلومات المنتج
                if ($data['slug'] && $data['slug'] != '') {
                    $slug = $this->CustomeSlug($data['slug']);
                } else {
                    $slug = $this->CustomeSlug($data['name']);
                }
                $product->update([
                    'name' => $data['name'],
                    'slug' => $slug,
                    'status' => $data['status'],
                    'short_description' => $data['short_description'],
                    'description' => $data['description'],
                    'price' => $data['price'],
                    'meta_title' => $data['meta_title'],
                    'meta_keywords' => $data['meta_keywords'],
                    'meta_description' => $data['meta_description']
                ]);

//                ///////// تحديث معرض الصور إذا كان موجودًا
                if ($request->hasFile('gallery')) {
                    foreach ($request->file('gallery') as $gallery) {
                        $gallery_name = $this->saveImage($gallery, 'assets/uploads/product_gallery');
                        $gallery_image = new ProductGallary();
                        $gallery_image->product_id = $product->id;
                        $gallery_image->image = $gallery_name;
                        $gallery_image->save();
                    }
                }


                DB::commit();
                // بعد تحديث المنتج بنجاح
                return Redirect::route('product.update', ['slug' => $product->slug])
                    ->with('Success_message', 'تم تعديل المنتج بنجاح');
            } catch (\Exception $e) {
                DB::rollback();
                return $this->error_message('حدث خطأ أثناء عملية التعديل.');
            }

        }
        // عرض صفحة التعديل
        return view('admin.Products.update', compact('product', 'gallaries'));
    }


    public function delete($id)
    {
        $product = Product::findOrFail($id);
        $product->delete();

        return $this->success_message(' تم حذف المنتج بنجاح  ');
    }

    public function delete_image_gallary($id)
    {
        $imageGallary = ProductGallary::findOrFail($id);
        $oldimage = public_path('assets/uploads/product_gallery/' . $imageGallary['image']);
        if (file_exists($oldimage)) {
            unlink($oldimage);
        }
        $imageGallary->delete();
        return $this->success_message(' تم حذف الصورة بنجاح  ');
    }
}
