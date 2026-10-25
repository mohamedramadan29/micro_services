<?php

namespace App\Http\Controllers\front;

use App\Http\Controllers\Controller;
use App\Http\Traits\Message_Trait;
use App\Http\Traits\Slug_Trait;
use App\Http\Traits\Upload_Images;
use App\Models\admin\Category;
use App\Models\admin\Service;
use App\Models\admin\SubCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Rule;

/**
 * «خدماتي المعروضة»: ready-made services a freelancer sells. Every new or edited service goes to the
 * team's review (status pending) before it shows on the site or can be bought.
 */
class serviceController extends Controller
{
    use Message_Trait;
    use Upload_Images;
    use Slug_Trait;

    public function index()
    {
        $services = Service::with('category:id,name', 'subcategory:id,name')->where('user_id', Auth::id())->latest('id')->get();
        $commission = \App\Services\Payments\RequestPaymentService::commissionRate();

        return view('website.service.index', compact('services', 'commission'));
    }

    public function getSubCategories($category_id)
    {
        $subCategories = SubCategory::where('parent_id', $category_id)->where('status', 1)->get();
        return response()->json($subCategories);
    }

    public function add(Request $request)
    {
        // Selling a service is a freelancer's job (clients request services instead).
        if (! Auth::user()->isFreelancer()) {
            return Redirect::to('dashboard')->withErrors(['error' => 'إضافة الخدمات متاحة لحسابات المستقلين فقط. لو عايز تشتغل كمستقل غيّر نوع حسابك من «تعديل الحساب».']);
        }
        if (! $request->isMethod('post')) {
            return view('website.service.add', $this->formData());
        }

        $data = $this->validated($request, true);
        $service = Service::create($this->attributes($data) + [
            'user_id' => Auth::id(),
            'image' => $this->saveImage($request->file('image'), public_path('assets/uploads/services')),
            'status' => Service::STATUS_PENDING,
        ]);
        $service->sendToReview();

        return redirect('service/index')->with('Success_message', 'تمت إضافة الخدمة، وتظهر للعملاء بعد مراجعة فريق نفّذها.');
    }

    public function update(Request $request, $id)
    {
        $service = Service::where('user_id', Auth::id())->findOrFail($id);
        if (! $request->isMethod('post')) {
            return view('website.service.update', $this->formData() + ['service' => $service]);
        }

        $data = $this->validated($request, false);
        $attributes = $this->attributes($data);
        if ($request->hasFile('image')) {
            $old = $service->image;
            $attributes['image'] = $this->saveImage($request->file('image'), public_path('assets/uploads/services'));
            if ($old) {
                @unlink(public_path('assets/uploads/services/' . basename($old)));
            }
        }
        $service->update($attributes);
        // An edited service is checked again before it shows (nobody changes it after its approval).
        $service->sendToReview();

        return redirect('service/index')->with('Success_message', 'تم حفظ التعديل، وتظهر الخدمة بعد مراجعة فريق نفّذها.');
    }

    public function delete($id)
    {
        try {
            $serivces = Service::where('user_id', Auth::id())->findOrFail($id);
            if ($serivces['image'] != '') {
                @unlink(public_path('assets/uploads/services/' . basename($serivces['image'])));
            }
            $serivces->delete();
            \Illuminate\Support\Facades\Cache::forget('admin-menu-badges');
            return redirect('service/index')->with('Success_message', 'تم حذف الخدمة.');
        } catch (\Exception $e) {
            return $this->exception_message($e);
        }
    }

    private function formData(): array
    {
        return [
            'categories' => Category::where('status', 1)->orderBy('id')->get(['id', 'name']),
            'subCategories' => SubCategory::where('status', 1)->orderBy('name')->get(['id', 'name', 'parent_id']),
            'commission' => \App\Services\Payments\RequestPaymentService::commissionRate(),
        ];
    }

    private function validated(Request $request, bool $adding): array
    {
        return $request->validate([
            'name' => 'required|string|min:8|max:150',
            'cat_id' => ['required', 'integer', Rule::exists('categories', 'id')->where('status', 1)],
            // The sub-category must belong to the chosen category.
            'sub_cat_id' => ['required', 'integer', Rule::exists('sub_categories', 'id')->where('status', 1)->where('parent_id', (int) $request->input('cat_id'))],
            'description' => 'required|string|min:20|max:5000',
            'image' => [$adding ? 'required' : 'nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:4096'],
            'price' => 'required|numeric|min:5|max:1000000',
            'tags' => 'nullable|string|max:500',
        ], [
            'name.required' => 'اكتب اسم الخدمة',
            'name.min' => 'اسم الخدمة قصير؛ اكتب ما ستنفّذه بالضبط (8 أحرف على الأقل)',
            'name.max' => 'اسم الخدمة طويل (150 حرفًا كحد أقصى)',
            'cat_id.required' => 'اختر القسم',
            'cat_id.exists' => 'القسم غير متاح',
            'sub_cat_id.required' => 'اختر الخدمة الفرعية',
            'sub_cat_id.exists' => 'اختر خدمة فرعية من نفس القسم',
            'description.required' => 'اكتب وصف الخدمة',
            'description.min' => 'الوصف قصير (20 حرفًا على الأقل)',
            'description.max' => 'الوصف طويل (5000 حرف كحد أقصى)',
            'image.required' => 'ارفع صورة للخدمة',
            'image.image' => 'اختر ملف صورة',
            'image.mimes' => 'الصورة يجب أن تكون jpg أو png أو webp',
            'image.max' => 'حجم الصورة يجب ألا يتجاوز 4 ميجابايت',
            'price.required' => 'اكتب سعر الخدمة',
            'price.numeric' => 'السعر يجب أن يكون رقمًا',
            'price.min' => 'أقل سعر 5 ج.م',
            'price.max' => 'السعر كبير جدًا',
        ]);
    }

    private function attributes(array $data): array
    {
        // Tags: «تصميم، شعار , هوية» → «تصميم,شعار,هوية» (unique, trimmed).
        $tags = collect(preg_split('/[,،]+/u', (string) ($data['tags'] ?? '')))->map(fn ($t) => trim($t))->filter()->unique()->take(10)->implode(',');

        return [
            'name' => trim($data['name']),
            'slug' => $this->CustomeSlug($data['name']),
            'cat_id' => $data['cat_id'],
            'sub_cat_id' => $data['sub_cat_id'],
            'description' => $data['description'],
            'price' => $data['price'],
            'tags' => $tags, // the column is NOT NULL: no tags = ''
        ];
    }
}
