<?php

namespace App\Http\Controllers\admin;

use App\Http\Traits\Slug_Trait;
use Illuminate\Http\Request;
use App\Models\admin\Category;
use App\Models\admin\SubCategory;
use App\Http\Traits\Message_Trait;
use App\Http\Controllers\Controller;
use App\Http\Traits\Upload_Images;
use App\Models\admin\nafizhaPortfolio;
use Illuminate\Support\Facades\Validator;

class NafizhaPortfolioController extends Controller
{
    use Message_Trait;
    use Slug_Trait;
    use Upload_Images;
    public function index()
    {
        $portfolios = nafizhaPortfolio::latest()->get();
        return view('admin.nafizha-portfolio.index', compact('portfolios'));
    }

    public function create()
    {
        $categories = Category::where('status', 1)->get();
        $sub_categories = SubCategory::where('status', 1)->get();
        return view('admin.nafizha-portfolio.create', compact('categories', 'sub_categories'));
    }

    public function store(Request $request)
    {
        $data = $request->all();

        $rules = [
            'title' => 'required',
            'description' => 'required',
            'image' => 'required|image|mimes:jpeg,png,jpg,webp|max:4096',
            'category' => 'required|exists:categories,id',
            'skills' => 'required|array',
        ];

        $messages = [
            'title.required' => 'من فضلك ادخل عنوان العمل',
            'description.required' => 'من فضلك ادخل وصف العمل',
            'image.required' => 'من فضلك ارفع الصورة الرئيسية',
            'image.image' => 'يجب أن يكون الملف صورة',
            'image.mimes' => 'الامتدادات المسموحة: jpeg, png, jpg, webp',
            'image.max' => 'حجم الصورة يجب ألا يزيد عن 4MB',
            'category.required' => 'من فضلك حدد القسم الرئيسي',
        ];

        $validator = Validator::make($data, $rules, $messages);
        if ($validator->fails()) {
            return redirect()->back()->withInput()->withErrors($validator);
        }

        // رفع الصورة الرئيسية
        $mainImage = $request->file('image');
        $filename = $this->saveImage($mainImage, public_path('assets/uploads/portfolios'));

        // رفع الصور الإضافية
        $additionalImages = [];
        if ($request->hasFile('additional_images')) {
            foreach ($request->file('additional_images') as $image) {
                $filenames = $this->saveImage($image, public_path('assets/uploads/portfolios'));
                $additionalImages[] = $filenames;
            }
        }

        $user_portfolio = new nafizhaPortfolio();
        // $user_portfolio->user_id = Auth::id();
        $user_portfolio->title = $data['title'];
        $user_portfolio->slug = $this->CustomeSlug($data['title']);
        $user_portfolio->description = $data['description'];
        $user_portfolio->link = $data['link'] ?? null;
        $user_portfolio->image = $filename;
        $user_portfolio->more_images = $additionalImages;
        // $user_portfolio->tools = $data['skills'];
        $user_portfolio->tools = is_array($data['skills'])
            ? implode(',', $data['skills'])
            : $data['skills'];
        $user_portfolio->category_id = $data['category'];
        $user_portfolio->status = 1;
        $user_portfolio->save();

        return $this->success_message('  تم اضافة العمل بنجاح  ');
    }

    public function edit($id)
    {
        $portfolio = nafizhaPortfolio::findOrFail($id);
        $categories = Category::where('status', 1)->get();
        $sub_categories = SubCategory::where('status', 1)->get();
        return view('admin.nafizha-portfolio.edit', compact('portfolio', 'categories', 'sub_categories'));
    }

    public function update(Request $request, $id)
    {
        $portfolio = nafizhaPortfolio::where('id', $id)->firstOrFail();

        $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'required|string',
            'image'       => 'nullable|image|mimes:jpeg,png,jpg,webp|max:4096',
            'category'    => 'required|exists:categories,id',
            'skills'      => 'required|array',
            'skills.*'    => 'exists:sub_categories,id',
            // 'link'        => 'nullable|url',
        ]);

        // رفع الصورة الرئيسية الجديدة إذا تم تحميلها
        if ($request->hasFile('image')) {
            // حذف الصورة القديمة
            if ($portfolio->image && file_exists(public_path('assets/uploads/portfolios/' . $portfolio->image))) {
                unlink(public_path('assets/uploads/portfolios/' . $portfolio->image));
            }
            $mainImage = $request->file('image');
            $filename = $this->saveImage($mainImage, public_path('assets/uploads/portfolios'));
        }
        // رفع الصور الإضافية الجديدة إذا تم تحميلها
        $additionalImages = [];
        if ($request->hasFile('additional_images')) {
            foreach ($request->file('additional_images') as $image) {
                $filenames = $this->saveImage($image, public_path('assets/uploads/portfolios'));
                $additionalImages[] = $filenames;
            }
        } else {
            $additionalImages = $portfolio->more_images ?? []; // الاحتفاظ بالصور القديمة
        }

        $portfolio->update([
            'title'         => $request->title,
            'slug'          => $this->CustomeSlug($request->title),
            'description'   => $request->description,
            'link'          => $request->link,
            'image'         => $filename,
            'more_images'   => $additionalImages, // نضمن إنه array نظيف
            'tools'         =>  is_array($request['skills'])
                ? implode(',', $request['skills'])
                : $request['skills'],
            'category_id'   => $request->category,
            'status'        => $request->status ?? $portfolio->status,
        ]);

        return $this->success_message('  تم تعديل العمل بنجاح  ');
        // return to_route('user.portfolio.edit', [$portfolio->id, $portfolio->slug])->with('Success_message', 'تم تعديل العمل بنجاح');
    }

    public function delete($id)
    {
        nafizhaPortfolio::findOrFail($id)->delete();
        return redirect()->back();
    }
}
