<?php

namespace App\Http\Controllers\admin;

use App\Http\Traits\Slug_Trait;
use Illuminate\Http\Request;
use App\Models\admin\Category;
use App\Models\admin\SubCategory;
use App\Http\Traits\Message_Trait;
use App\Http\Controllers\Controller;
use App\Models\admin\nafizhaPortfolio;
use Illuminate\Support\Facades\Validator;

class NafizhaPortfolioController extends Controller
{
    use Message_Trait;
    use Slug_Trait;
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
            'image' => 'required|string', // دلوقتي string (اسم الملف)
            'category' => 'required|exists:categories,id',
            'skills' => 'required|array',
        ];

        $messages = [
            'title.required' => 'من فضلك ادخل عنوان العمل',
            'description.required' => 'من فضلك ادخل وصف العمل',
            'image.required' => 'من فضلك ارفع الصورة الرئيسية',
            'category.required' => 'من فضلك حدد القسم الرئيسي',
        ];

        $validator = Validator::make($data, $rules, $messages);
        if ($validator->fails()) {
            return redirect()->back()->withInput()->withErrors($validator);
        }

        // الصورة الرئيسية جاية كـ string (اسم الملف)
        $path = $data['image'];

        // الصور الإضافية جاية كـ array من أسماء الملفات
        $path_images = $request->has('files') ? $request->file('files') : [];
        // لا! هنا مش files حقيقية، ده أسماء
        $path_images = $request->input('files', []); // array من strings

        $user_portfolio = new nafizhaPortfolio();
        // $user_portfolio->user_id = Auth::id();
        $user_portfolio->title = $data['title'];
        $user_portfolio->slug = $this->CustomeSlug($data['title']);
        $user_portfolio->description = $data['description'];
        $user_portfolio->link = $data['link'] ?? null;
        $user_portfolio->image = $path;
        $user_portfolio->more_images = $path_images;
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
            'image'       => 'required|string', // اسم الملف من الـ hidden
            'category'    => 'required|exists:categories,id',
            'skills'      => 'required|array',
            'skills.*'    => 'exists:sub_categories,id',
            // 'link'        => 'nullable|url',
        ]);

        // الصورة الرئيسية (جاية كـ string من hidden input)
        $mainImage = $request->image;

        // الصور الإضافية (array من strings)
        $additionalImages = $request->input('files', []);

        $portfolio->update([
            'title'         => $request->title,
            'slug'          => $this->CustomeSlug($request->title),
            'description'   => $request->description,
            'link'          => $request->link,
            'image'         => $mainImage,
            'more_images'   => $additionalImages, // نضمن إنه array نظيف
            'tools'         =>  is_array($request['skills'])
                ? implode(',', $request['skills'])
                : $request['skills'],
            'category_id'   => $request->category,
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
