<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Http\Traits\Slug_Trait;
use App\Http\Traits\Upload_Images;
use App\Models\admin\Category;
use App\Models\admin\SubCategory;
use App\Models\HelpRequest;
use App\Models\ServiceForm;
use App\Models\ServiceRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Groups (categories) and services (sub-categories) of the services page,
 * managed without the programmer (client brief, pages 5 and 9). Existing
 * services are hidden, never deleted, once anything uses them.
 */
class CatalogController extends Controller
{
    use Slug_Trait;
    use Upload_Images;

    private const IMAGE_DIR = 'assets/uploads/service_category';

    public function groups()
    {
        $groups = Category::ordered()->withCount('subCategories')->get();

        return view('admin.catalog.groups', compact('groups'));
    }

    public function editGroup(?Category $group = null)
    {
        return view('admin.catalog.group-form', ['group' => $group ?? new Category(['status' => 1, 'sort_order' => (int) Category::max('sort_order') + 1])]);
    }

    public function saveGroup(Request $request, ?Category $group = null)
    {
        $data = $request->validate($this->rules(), $this->messages());
        $group ??= new Category();
        $group->fill([
            'name' => $data['name'],
            'slug' => $this->CustomeSlug($data['name']),
            'description' => $data['description'] ?? null,
            'icon' => $data['icon'] ?? null,
            'sort_order' => (int) ($data['sort_order'] ?? 0),
            'status' => (int) $request->boolean('status'),
        ]);
        $this->storeImage($request, $group);
        $group->save();

        return redirect()->route('admin.catalog.groups')->with('success', 'تم حفظ المجموعة «' . $group->name . '». التغيير ظاهر الآن في صفحة الخدمات.');
    }

    public function deleteGroup(Category $group)
    {
        if ($group->subCategories()->exists()) {
            return back()->withErrors('لا يمكن حذف مجموعة فيها خدمات. انقل خدماتها أو أخفِ المجموعة.');
        }
        $group->delete();

        return back()->with('success', 'تم حذف المجموعة.');
    }

    public function services(Request $request)
    {
        $services = SubCategory::with('category')
            ->when($request->integer('group'), fn ($q, $group) => $q->where('parent_id', $group))
            ->when($request->filled('q'), fn ($q) => $q->where('name', 'like', '%' . str_replace(['%', '_'], ['\%', '\_'], $request->query('q')) . '%'))
            ->when($request->filled('visible'), fn ($q) => $q->where('status', $request->query('visible') === '1' ? 1 : 0))
            ->get()
            ->sortBy(fn (SubCategory $s) => [$s->category?->sort_order ?? PHP_INT_MAX, $s->parent_id, $s->sort_order, $s->id])
            ->values();
        $groups = Category::ordered()->get(['id', 'name']);
        $withForm = \App\Models\ServiceForm::published()->pluck('sub_category_id')->flip();

        return view('admin.catalog.services', compact('services', 'groups', 'withForm'));
    }

    public function editService(?SubCategory $service = null)
    {
        $groups = Category::ordered()->get(['id', 'name']);

        return view('admin.catalog.service-form', [
            'service' => $service ?? new SubCategory(['status' => 1, 'parent_id' => request()->integer('group') ?: null]),
            'groups' => $groups,
        ]);
    }

    public function saveService(Request $request, ?SubCategory $service = null)
    {
        $data = $request->validate($this->rules() + [
            'parent_id' => 'required_without:new_group|nullable|integer|exists:categories,id',
            'new_group' => 'nullable|string|max:255',
            'link' => ['nullable', 'string', 'max:500', 'regex:#^(/(?!/)[^\s]*|https?://[^\s]+)$#i'],
            'button_text' => 'nullable|string|max:100',
        ], $this->messages() + [
            'parent_id.required_without' => 'اختر المجموعة أو اكتب اسم مجموعة جديدة',
            'link.regex' => 'الرابط يبدأ بـ / أو https://',
        ]);

        DB::transaction(function () use ($request, $data, &$service) {
            $groupId = $data['parent_id'] ?? null;
            if (! empty($data['new_group'])) {
                // «اختار مجموعة أو أنشئ مجموعة» from the service form.
                $groupId = Category::firstOrCreate(['name' => trim($data['new_group'])], [
                    'slug' => $this->CustomeSlug($data['new_group']),
                    'sort_order' => (int) Category::max('sort_order') + 1,
                    'status' => 1,
                ])->id;
            }
            $service ??= new SubCategory();
            $service->fill([
                'name' => $data['name'],
                'slug' => $this->CustomeSlug($data['name']),
                'parent_id' => $groupId,
                'description' => $data['description'] ?? null,
                'icon' => $data['icon'] ?? null,
                'sort_order' => (int) ($data['sort_order'] ?? 0),
                'link' => $data['link'] ?? null,
                'button_text' => $data['button_text'] ?? null,
                'status' => (int) $request->boolean('status'),
            ]);
            $this->storeImage($request, $service);
            $service->save();
        });

        return redirect()->route('admin.catalog.services', ['group' => $service->parent_id])
            ->with('success', 'تم حفظ الخدمة «' . $service->name . '»' . ($service->status ? '، وهي ظاهرة الآن في صفحة الخدمات.' : ' وهي مخفية.'));
    }

    /** Only a service nothing uses can be deleted; the others are hidden instead. */
    public function deleteService(SubCategory $service)
    {
        $used = ServiceForm::where('sub_category_id', $service->id)->exists()
            || ServiceRequest::where('sub_category_id', $service->id)->exists()
            || HelpRequest::where('sub_category_id', $service->id)->exists()
            || DB::table('freelancer_specializations')->where('sub_category_id', $service->id)->exists();
        if ($used) {
            return back()->withErrors('هذه الخدمة مستخدمة (نموذج طلب أو طلبات أو تخصصات مستقلين). أخفِها بدل حذفها حتى لا تضيع بياناتها.');
        }
        $service->delete();

        return back()->with('success', 'تم حذف الخدمة.');
    }

    private function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'icon' => ['nullable', 'string', 'regex:/^bi-[a-z0-9-]+$/'],
            'sort_order' => 'nullable|integer|min:0|max:9999',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ];
    }

    private function messages(): array
    {
        return [
            'name.required' => 'الاسم مطلوب',
            'icon.regex' => 'اسم الأيقونة غير صحيح (مثال: bi-palette)',
            'image.image' => 'الملف يجب أن يكون صورة',
            'image.mimes' => 'الصيغ المسموحة: jpg, png, webp',
            'image.max' => 'أقصى حجم للصورة 2 ميجابايت',
        ];
    }

    private function storeImage(Request $request, $model): void
    {
        if ($request->boolean('remove_image')) {
            $model->image = null;
        }
        if ($request->hasFile('image')) {
            $model->image = $this->saveImage($request->file('image'), public_path(self::IMAGE_DIR));
        }
    }
}
