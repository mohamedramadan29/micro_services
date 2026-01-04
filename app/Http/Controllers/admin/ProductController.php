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

                $rules = [
                    'name' => 'required|string|max:255',
                    'type' => 'required|in:physical,digital',
                    'description' => 'required',
                    'price' => 'required|numeric|min:0',
                    'status' => 'required|in:0,1',
                ];

                // لو المنتج رقمي → مطلوب مسار الملف (من Livewire hidden input)
                if (isset($data['type']) && $data['type'] === 'digital') {
                    $rules['digital_file_path'] = 'required|string';
                    $messages['digital_file_path.required'] = 'من فضلك ارفع الملف الرقمي';
                }

                // صورة المنتج الرئيسية
                if ($request->hasFile('image')) {
                    $rules['image'] = 'image|mimes:jpg,png,jpeg,webp';
                }

                $messages = [
                    'name.required' => 'من فضلك ادخل اسم المنتج',
                    'type.required' => 'من فضلك حدد نوع المنتج',
                    'type.in' => 'نوع المنتج غير صالح',
                    'description.required' => 'من فضلك ادخل الوصف الخاص بالمنتج',
                    'price.required' => 'من فضلك ادخل سعر المنتج',
                    'price.numeric' => 'سعر المنتج يجب أن يكون رقم',
                    'image.image' => 'من فضلك ادخل صورة صحيحة',
                    'image.mimes' => 'نوع الصورة يجب أن يكون jpg, png, jpeg, webp',
                ];

                $validator = Validator::make($data, $rules, $messages ?? []);
                if ($validator->fails()) {
                    return Redirect::back()->withInput()->withErrors($validator);
                }

                // رفع الصورة الرئيسية
                $file_name = null;
                if ($request->hasFile('image')) {
                    $file_name = $this->saveImage($request->image, 'assets/uploads/product_images');
                }

                // رفع الفيديو (لو موجود)
                $video_name = null;
                if ($request->hasFile('video')) {
                    if ($request->file('video')->isValid()) {
                        $video_name = $this->saveImage($request->video, 'assets/uploads/product_videos');
                    } else {
                        return back()->withErrors(['video' => 'هناك مشكلة في رفع الفيديو.']);
                    }
                }

                // الملف الرقمي: نأخذ اسمه من hidden input (digital_file_path) اللي جاي من Livewire
                $digital_file_name = ($data['type'] === 'digital') ? $data['digital_file_path'] : null;

                // التحقق من تكرار اسم المنتج
                $count_old_product = Product::where('name', $data['name'])->count();
                if ($count_old_product > 0) {
                    return Redirect::back()->withInput()->withErrors('اسم المنتج متواجد من قبل، من فضلك ادخل اسم جديد');
                }

                DB::beginTransaction();

                $product = new Product();
                $product->name = $data['name'];

                // Slug
                if (!empty($data['slug'])) {
                    $slug = $this->CustomeSlug($data['slug']);
                } else {
                    $slug = $this->CustomeSlug($data['name']);
                }
                $product->slug = $slug;

                $product->type = $data['type'];
                $product->status = $data['status'] ?? 1;
                $product->short_description = $data['short_description'] ?? null;
                $product->description = $data['description'];
                $product->price = $data['price'];
                $product->discount = $data['discount'] ?? null;
                $product->meta_title = $data['meta_title'] ?? null;
                $product->meta_keywords = $data['meta_keywords'] ?? null;
                $product->meta_description = $data['meta_description'] ?? null;
                $product->image = $file_name;
                $product->video = $video_name;
                $product->digital_file = $digital_file_name; // حفظ اسم الملف الرقمي من Livewire
                $product->save();

                // رفع صور المعرض (gallery)
                if ($request->hasFile('gallery')) {
                    foreach ($request->file('gallery') as $gallery) {
                        $gallery_name = $this->saveImage($gallery, 'assets/uploads/product_gallery');
                        $gallery_image = new productGallary();
                        $gallery_image->product_id = $product->id;
                        $gallery_image->image = $gallery_name;
                        $gallery_image->save();
                    }
                }

                DB::commit();

                return $this->success_message('تم إضافة المنتج بنجاح');
            } catch (\Exception $e) {
                DB::rollback();
                return $this->exception_message($e);
            }
        }

        return view('admin.Products.add');
    }

    public function update(Request $request, $slug)
    {
        $product = Product::where('slug', $slug)->first();
        if(!$product){
            return to_route('admin.products.index');
        }
        $gallaries = ProductGallary::where('product_id', $product->id)->get();

        if ($request->isMethod('post')) {
            try {
                $data = $request->all();

                $rules = [
                    'name' => 'required|string|max:255',
                    'type' => 'required|in:physical,digital',
                    'description' => 'required',
                    'price' => 'required|numeric|min:0',
                    'status' => 'required|in:0,1',
                ];

                // صورة المنتج الرئيسية (اختياري في التعديل)
                if ($request->hasFile('image')) {
                    $rules['image'] = 'image|mimes:jpg,png,jpeg,webp';
                }

                // لو المنتج رقمي ورفع ملف جديد → نطلب digital_file_path كـ string (من Livewire)
                if (isset($data['type']) && $data['type'] === 'digital' && $request->filled('digital_file_path')) {
                    $rules['digital_file_path'] = 'string';
                }

                $messages = [
                    'name.required' => 'من فضلك ادخل اسم المنتج',
                    'type.required' => 'من فضلك حدد نوع المنتج',
                    'type.in' => 'نوع المنتج غير صالح',
                    'description.required' => 'من فضلك ادخل الوصف الخاص بالمنتج',
                    'price.required' => 'من فضلك ادخل سعر المنتج',
                    'price.numeric' => 'سعر المنتج يجب أن يكون رقم',
                    'image.image' => 'من فضلك ادخل صورة صحيحة',
                    'image.mimes' => 'نوع الصورة يجب أن يكون jpg, png, jpeg, webp',
                ];

                $validator = Validator::make($data, $rules, $messages ?? []);
                if ($validator->fails()) {
                    return Redirect::back()->withInput()->withErrors($validator);
                }

                DB::beginTransaction();

                // تحديث الصورة الرئيسية إذا تم رفع واحدة جديدة
                if ($request->hasFile('image')) {
                    // حذف القديمة
                    if ($product->image) {
                        $old_image = public_path('assets/uploads/product_images/' . $product->image);
                        if (file_exists($old_image)) {
                            unlink($old_image);
                        }
                    }
                    $file_name = $this->saveImage($request->image, public_path('assets/uploads/product_images'));
                    $product->image = $file_name;
                }

                // تحديث الفيديو إذا تم رفع واحد جديد
                if ($request->hasFile('video')) {
                    if ($product->video) {
                        $old_video = public_path('assets/uploads/product_videos/' . $product->video);
                        if (file_exists($old_video)) {
                            unlink($old_video);
                        }
                    }
                    $video_name = $this->saveImage($request->video, public_path('assets/uploads/product_videos'));
                    $product->video = $video_name;
                }

                // تحديث الملف الرقمي (من Livewire)
                $digital_file_name = $product->digital_file; // احتفظ بالقديم افتراضيًا

                if ($data['type'] === 'digital' && $request->filled('digital_file_path')) {
                    $new_digital_file = $request->digital_file_path;

                    // لو الملف الجديد مختلف عن القديم → نحذف القديم
                    if ($product->digital_file && $product->digital_file !== $new_digital_file) {
                        $old_digital_path = public_path('assets/uploads/digital_products/' . $product->digital_file);
                        if (file_exists($old_digital_path)) {
                            unlink($old_digital_path);
                        }
                    }

                    $digital_file_name = $new_digital_file;
                }

                // Slug
                if (!empty($data['slug'])) {
                    $new_slug = $this->CustomeSlug($data['slug']);
                } else {
                    $new_slug = $this->CustomeSlug($data['name']);
                }

                // تحديث باقي البيانات
                $product->update([
                    'name' => $data['name'],
                    'slug' => $new_slug,
                    'type' => $data['type'],
                    'status' => $data['status'] ?? 1,
                    'short_description' => $data['short_description'] ?? null,
                    'description' => $data['description'],
                    'price' => $data['price'],
                    'discount' => $data['discount'] ?? null,
                    'meta_title' => $data['meta_title'] ?? null,
                    'meta_keywords' => $data['meta_keywords'] ?? null,
                    'meta_description' => $data['meta_description'] ?? null,
                    'digital_file' => $digital_file_name,
                ]);

                // إضافة صور معرض جديدة
                if ($request->hasFile('gallery')) {
                    foreach ($request->file('gallery') as $gallery) {
                        $gallery_name = $this->saveImage($gallery, public_path('assets/uploads/product_gallery'));
                        $gallery_image = new ProductGallary();
                        $gallery_image->product_id = $product->id;
                        $gallery_image->image = $gallery_name;
                        $gallery_image->save();
                    }
                }

                DB::commit();

                return Redirect::route('admin.products.index')->with('Success_message', 'تم تعديل المنتج بنجاح');
            } catch (\Exception $e) {
                DB::rollback();
                return $this->exception_message($e);
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
    ##########################################

    public function uploadChunk(Request $request, $id)
    {
        $file = $request->file('chunk');
        $chunkIndex = $request->input('chunkIndex');
        $fileIdentifier = $request->input('fileIdentifier');
        $totalChunks = $request->input('totalChunks');

        $uploadPath = storage_path("app/chunks/$fileIdentifier");
        if (!file_exists($uploadPath)) {
            mkdir($uploadPath, 0777, true);
        }

        $file->move($uploadPath, "chunk_$chunkIndex");

        return response()->json(['status' => 'success', 'chunkIndex' => $chunkIndex]);
    }

    public function mergeChunks(Request $request, $id)
    {
        $fileIdentifier = $request->input('fileIdentifier');
        $originalFileName = $request->input('originalFileName');
        $uploadPath = storage_path("app/chunks/$fileIdentifier");
        $finalPath = public_path("assets/uploads/product_videos");

        if (!file_exists($finalPath)) {
            mkdir($finalPath, 0777, true);
        }

        $finalFilePath = "$finalPath/$originalFileName";
        $finalFile = fopen($finalFilePath, 'wb');

        $totalChunks = $request->input('totalChunks');
        for ($i = 0; $i < $totalChunks; $i++) {
            $chunkFile = fopen("$uploadPath/chunk_$i", 'rb');
            fwrite($finalFile, fread($chunkFile, filesize("$uploadPath/chunk_$i")));
            fclose($chunkFile);
        }

        fclose($finalFile);

        // حذف الأجزاء بعد الدمج
        array_map('unlink', glob("$uploadPath/*"));
        rmdir($uploadPath);

        // تحديث الفيديو في قاعدة البيانات للمنتج المحدد
        $product = Product::find($id);
        if ($product) {
            $product->video = "$originalFileName";
            $product->save();
        }

        return response()->json(['status' => 'completed', 'video_path' => "assets/uploads/product_videos/$originalFileName"]);
    }
}
