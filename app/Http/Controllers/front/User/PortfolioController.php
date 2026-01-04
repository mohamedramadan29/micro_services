<?php

namespace App\Http\Controllers\front\User;

use App\Http\Traits\Message_Trait;
use App\Http\Traits\Slug_Trait;
use App\Http\Traits\Upload_Images;
use Illuminate\Http\Request;
use App\Models\admin\Category;
use App\Models\admin\SubCategory;
use App\Models\front\UserPortfolio;
use App\Http\Controllers\Controller;
use App\Http\Utils\Imagemanager;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;

class PortfolioController extends Controller
{
    use Message_Trait;
    use Upload_Images;
    use Slug_Trait;

    public $imagemanager;
    public function __construct(Imagemanager $imagemanager)
    {
        $this->imagemanager = $imagemanager;
    }
    public function index()
    {
        $portfolios = UserPortfolio::where('user_id', Auth::id())->latest()->get();
        return view('website.user.user_portfolio.index', compact('portfolios'));
    }

    ############# Create Page
    public function create()
    {
        $categories = Category::where('status', 1)->get();
        $sub_categories = SubCategory::where('status', 1)->get();
        return view('website.user.user_portfolio.create', compact('categories', 'sub_categories'));
    }
    ########## Store

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

        $user_portfolio = new UserPortfolio();
        $user_portfolio->user_id = Auth::id();
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
        $user_portfolio->save();

        return $this->success_message(' تم اضافة العمل بنجاح من فضلك انتظر التفعيل من الادارة  ');
    }
    ######## Edit Page

    public function edit($id, $slug)
    {
        $portfolio = UserPortfolio::where('id', $id)->where('slug', $slug)->first();

        if ($portfolio->user_id != Auth::id()) {
            abort(404);
        }
        $categories = Category::where('status', 1)->get();
        $sub_categories = SubCategory::where('status', 1)->get();
        return view('website.user.user_portfolio.edit', compact('portfolio', 'categories', 'sub_categories'));
    }

    ######### Update Page

    public function update(Request $request, $id, $slug)
    {
        $portfolio = UserPortfolio::where('id', $id)->where('slug', $slug)->firstOrFail();

        if ($portfolio->user_id != Auth::id()) {
            abort(403);
        }

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

        return to_route('user.portfolio.edit', [$portfolio->id, $portfolio->slug])->with('Success_message', 'تم تعديل العمل بنجاح');
    }

    ######## Delete Page
    public function delete($id)
    {
        $portfolio = UserPortfolio::find($id);
        if ($portfolio->user_id != Auth::id()) {
            abort(403);
        }
        ######### Delete Portfolio Images
        // if ($portfolio->image) {

        // }
        $portfolio->delete();

        return to_route('user.portfolio.index')->with('Success_message', 'تم حذف العمل بنجاح');
    }
}
