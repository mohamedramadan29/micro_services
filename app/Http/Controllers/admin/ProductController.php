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
                    'type' => 'required|in:physical,digital', // قاعدة جديدة لنوع المنتج
                    'description' => 'required'
                ];

                // قواعد ديناميكية بناءً على النوع
                if ($data['type'] === 'digital') {
                    $rules['digital_file'] = 'required|file|mimes:pdf,zip,rar,exe,mp3,mp4|max:102400'; // 100MB max
                }

                if ($request->hasFile('image')) {
                    $rules['image'] = 'image|mimes:jpg,png,jpeg,webp';
                }

                $messages = [
                    'name.required' => ' من فضلك ادخل اسم المنتج  ',
                    'type.required' => ' من فضلك حدد نوع المنتج ',
                    'type.in' => ' نوع المنتج غير صالح ',
                    'description.required' => ' من فضلك ادخل الوصف الخاص بالمنتج ',
                    'digital_file.required' => ' من فضلك ارفع الملف الرقمي ',
                    'digital_file.mimes' => ' نوع الملف غير مدعوم (pdf, zip, rar, exe, mp3, mp4) ',
                    'digital_file.max' => ' حجم الملف كبير جداً (حد أقصى 100MB) ',
                    'image.mimes' => 'من فضلك يجب ان يكون نوع الصورة jpg,png,jpeg,webp',
                    'image.image' => 'من فضلك ادخل الصورة بشكل صحيح',
                ];

                $validator = Validator::make($data, $rules, $messages);
                if ($validator->fails()) {
                    return Redirect::back()->withInput()->withErrors($validator);
                }

                $file_name = null;
                if ($request->hasFile('image')) {
                    $file_name = $this->saveImage($request->image, public_path('assets/uploads/product_images'));
                }

                ########## Check If Product Has Video
                $video_name = null;
                if ($request->hasFile('video')) {
                    if ($request->file('video')->isValid()) {
                        $video_name = $this->saveImage($request->video, public_path('assets/uploads/product_videos'));
                    } else {
                        return back()->withErrors(['video' => 'هناك مشكلة في رفع الفيديو.']);
                    }
                }

                /////// رفع الملف الرقمي إذا كان النوع رقمي
                $digital_file_name = null;
                if ($request->hasFile('digital_file')) {
                    $digital_file_name = $this->saveImage($request->digital_file, public_path('assets/uploads/digital_products')); // مسار جديد للملفات الرقمية
                }

                /////// Check if This Product In Db Or Not
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
                $product->type = $data['type']; // حفظ نوع المنتج الجديد
                $product->status = $data['status'];
                $product->short_description = $data['short_description'];
                $product->description = $data['description'];
                $product->price = $data['price'];
                $product->discount = $data['discount'] ?? null;
                $product->meta_title = $data['meta_title'];
                $product->meta_keywords = $data['meta_keywords'];
                $product->meta_description = $data['meta_description'];
                $product->image = $file_name;
                $product->digital_file = $request['digital_file_path']; // حفظ مسار الملف الرقمي
                // $product->video = $video_name;
                $product->save();

                ///////// Check If Product Gallary Not Empty
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
            $data = $request->all();
            $rules = [
                'name' => 'required|string|max:255',
                'type' => 'required|in:physical,digital', // قاعدة جديدة لنوع المنتج
            ];

            // قواعد ديناميكية بناءً على النوع
            if ($data['type'] === 'digital') {
                $rules['digital_file'] = 'file|mimes:pdf,zip,rar,exe,mp3,mp4|max:102400'; // 100MB max، غير مطلوب لأنه تحديث
            }

            if ($request->hasFile('image')) {
                $rules['image'] = 'image|mimes:jpg,png,jpeg,webp';
            }

            $messages = [
                'name.required' => ' من فضلك ادخل اسم المنتج  ',
                'type.required' => ' من فضلك حدد نوع المنتج ',
                'type.in' => ' نوع المنتج غير صالح ',
                'digital_file.mimes' => ' نوع الملف غير مدعوم (pdf, zip, rar, exe, mp3, mp4) ',
                'digital_file.max' => ' حجم الملف كبير جداً (حد أقصى 100MB) ',
                'image.mimes' => 'من فضلك يجب ان يكون نوع الصورة jpg,png,jpeg,webp',
                'image.image' => 'من فضلك ادخل الصورة بشكل صحيح',
            ];

            $validator = Validator::make($data, $rules, $messages);
            if ($validator->fails()) {
                return Redirect::back()->withInput()->withErrors($validator);
            }

            DB::beginTransaction();

            try {
                /////// تحديث الصورة إذا كانت موجودة
                if ($request->hasFile('image')) {
                    $old_image = public_path('assets/uploads/product_images/' . $product->image);
                    if (file_exists($old_image)) {
                        unlink($old_image);
                    }
                    $file_name = $this->saveImage($request->image, public_path('assets/uploads/product_images'));
                    $product->update(['image' => $file_name]);
                }

                ############## Check If Update Video
                if ($request->hasFile('video')) {
                    $old_video = public_path('assets/uploads/product_videos/' . $product->video);
                    if (file_exists($old_video)) {
                        unlink($old_video);
                    }
                    $video_name = $this->saveImage($request->video, public_path('assets/uploads/product_videos'));
                    $product->update(['video' => $video_name]);
                }

                /////// تحديث الملف الرقمي إذا كان النوع رقمي وتم رفع ملف جديد
                if ($data['type'] === 'digital' && $request->hasFile('digital_file')) {
                    // حذف الملف القديم إذا كان موجودًا
                    if ($product->digital_file) {
                        $old_digital_file = public_path('assets/uploads/digital_products/' . $product->digital_file);
                        if (file_exists($old_digital_file)) {
                            unlink($old_digital_file);
                        }
                    }
                    $digital_file_name = $this->saveImage($request->digital_file, public_path('assets/uploads/digital_products'));
                    $product->update(['digital_file' => $digital_file_name]);
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
                    'type' => $data['type'], // تحديث النوع
                    'status' => $data['status'],
                    'short_description' => $data['short_description'],
                    'description' => $data['description'],
                    'price' => $data['price'],
                    'discount' => $data['discount'] ?? null,
                    'meta_title' => $data['meta_title'],
                    'meta_keywords' => $data['meta_keywords'],
                    'meta_description' => $data['meta_description']
                ]);

                ///////// تحديث معرض الصور إذا كان موجودًا
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
