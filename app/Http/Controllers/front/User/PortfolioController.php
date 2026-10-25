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
            'image' => ['required', 'string', 'max:255', 'regex:/^[\w.\-]+$/'], // اسم الملف فقط (بدون مسارات)
            'files' => 'nullable|array',
            'files.*' => ['string', 'max:255', 'regex:/^[\w.\-]+$/'],
            'category' => 'required|exists:categories,id',
            'skills' => 'required|array',
            'skills.*' => 'exists:sub_categories,id',
            'link' => ['nullable', 'url', 'regex:/^https?:\/\//i', 'max:500'],
        ];

        $messages = [
            'title.required' => 'من فضلك ادخل عنوان العمل',
            'description.required' => 'من فضلك ادخل وصف العمل',
            'image.required' => 'من فضلك ارفع الصورة الرئيسية',
            'category.required' => 'من فضلك حدد القسم الرئيسي',
            'skills.required' => 'اختر مهارة واحدة على الأقل',
            'link.url' => 'الرابط غير صحيح',
            'link.regex' => 'الرابط يجب أن يبدأ بـ http أو https',
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
        $user_portfolio->sendToReview();
        Auth::user()->markFreelancerProfileCompleted();

        return to_route('user.portfolio.index')->with('Success_message', 'تمت إضافة العمل، ويظهر في ملفك بعد مراجعة فريق نفّذها.');
    }
    ######## Edit Page

    public function edit($id, $slug)
    {
        $portfolio = UserPortfolio::where('id', $id)->where('slug', $slug)->firstOrFail();

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
            'image'       => ['required', 'string', 'max:255', 'regex:/^[\w.\-]+$/'], // اسم الملف من الـ hidden (بدون مسارات)
            'files'       => 'nullable|array',
            'files.*'     => ['string', 'max:255', 'regex:/^[\w.\-]+$/'],
            'category'    => 'required|exists:categories,id',
            'skills'      => 'required|array',
            'skills.*'    => 'exists:sub_categories,id',
            'link'        => ['nullable', 'url', 'regex:/^https?:\/\//i', 'max:500'],
        ], [
            'title.required' => 'من فضلك ادخل عنوان العمل',
            'description.required' => 'من فضلك ادخل وصف العمل',
            'image.required' => 'من فضلك ارفع الصورة الرئيسية',
            'category.required' => 'من فضلك حدد القسم الرئيسي',
            'skills.required' => 'اختر مهارة واحدة على الأقل',
            'link.url' => 'الرابط غير صحيح',
            'link.regex' => 'الرابط يجب أن يبدأ بـ http أو https',
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
        // An edited work is checked again before it shows (nobody changes a work after its approval).
        $portfolio->sendToReview();

        return to_route('user.portfolio.index')->with('Success_message', 'تم حفظ التعديل، ويظهر العمل بعد مراجعة فريق نفّذها.');
    }

    ######## Delete Page
    public function delete($id)
    {
        $portfolio = UserPortfolio::findOrFail($id);
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
