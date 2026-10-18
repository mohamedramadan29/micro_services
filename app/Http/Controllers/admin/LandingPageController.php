<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\ContentMedia;
use App\Models\LandingPage;
use App\Services\Landing\LandingBuilder;
use App\Services\Landing\LandingSections;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use RuntimeException;

/** «صفحات الهبوط» (client file «تعديلات», item 07): pages built from sections, draft → preview → publish. */
class LandingPageController extends Controller
{
    public function __construct(private LandingBuilder $builder)
    {
    }

    public function index()
    {
        return view('admin.landing.index', ['pages' => LandingPage::latest('id')->get()]);
    }

    public function store(Request $request)
    {
        $request->merge(['slug' => Str::slug((string) $request->input('slug')) ?: null]);
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => ['required', 'string', 'max:80', 'regex:/^[a-z0-9-]+$/', Rule::unique('landing_pages', 'slug')],
            'template' => 'required|in:starter,blank',
        ], [
            'title.required' => 'اكتب اسم الصفحة',
            'slug.required' => 'اكتب رابط الصفحة بالإنجليزية',
            'slug.regex' => 'الرابط يكون بحروف إنجليزية صغيرة وأرقام وشرطات فقط',
            'slug.unique' => 'هذا الرابط مستخدم لصفحة أخرى',
        ]);
        $page = $this->builder->create(['title' => $data['title'], 'slug' => $data['slug']], $data['template'] === 'starter');

        return redirect()->route('admin.landing.edit', $page)->with('success', 'أُنشئت الصفحة كمسودة. عدّل أقسامها ثم اضغط «نشر».');
    }

    public function edit(LandingPage $page)
    {
        return view('admin.landing.edit', [
            'page' => $page,
            'sections' => $page->working(),
            'types' => LandingSections::all(),
            'media' => ContentMedia::latest('id')->get(),
        ]);
    }

    /** Page settings are saved at once (title, link, SEO, header). */
    public function settings(Request $request, LandingPage $page)
    {
        $request->merge(['slug' => Str::slug((string) $request->input('slug')) ?: null]);
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => ['required', 'string', 'max:80', 'regex:/^[a-z0-9-]+$/', Rule::unique('landing_pages', 'slug')->ignore($page->id)],
            'meta_description' => 'nullable|string|max:300',
            'og_image_id' => ['nullable', 'integer', Rule::exists('content_media', 'id')],
            'show_site_nav' => 'boolean',
            'header_button_text' => 'nullable|string|max:60',
            'header_button_link' => ['nullable', 'string', 'max:500', 'regex:#^(/(?!/)[^\s]*|\#[A-Za-z0-9_-]+|https?://[^\s]+)$#i'],
        ], [
            'title.required' => 'اكتب اسم الصفحة',
            'slug.regex' => 'الرابط يكون بحروف إنجليزية صغيرة وأرقام وشرطات فقط',
            'slug.unique' => 'هذا الرابط مستخدم لصفحة أخرى',
            'header_button_link.regex' => 'رابط زر الشريط العلوي غير صحيح (ابدأ بـ / أو # أو https://)',
        ]);
        $page->update($data + ['show_site_nav' => $request->boolean('show_site_nav'), 'updated_by' => $request->user()->id]);

        return back()->with('success', 'حُفظت إعدادات الصفحة.');
    }

    public function addSection(Request $request, LandingPage $page)
    {
        $request->validate(['type' => ['required', Rule::in(array_keys(LandingSections::all()))]], ['type.*' => 'اختر نوع القسم']);

        return $this->run($page, function () use ($page, $request) {
            $section = $this->builder->add($page, $request->input('type'));

            return ['أُضيف القسم في آخر الصفحة. عدّل محتواه ثم انشر.', $section['id']];
        });
    }

    public function updateSection(Request $request, LandingPage $page, string $section)
    {
        return $this->run($page, function () use ($page, $request, $section) {
            $this->builder->update($page, $section, $request->only('fields', 'items'));

            return ['حُفظ القسم كمسودة. اضغط «نشر» ليظهر للزوار.', $section];
        });
    }

    public function sectionAction(LandingPage $page, string $section, string $action)
    {
        return $this->run($page, function () use ($page, $section, $action) {
            match ($action) {
                'up' => $this->builder->move($page, $section, -1),
                'down' => $this->builder->move($page, $section, 1),
                'toggle' => $this->builder->toggle($page, $section),
                'duplicate' => $this->builder->duplicate($page, $section),
                'delete' => $this->builder->remove($page, $section),
            };

            return [$action === 'delete' ? 'حُذف القسم من المسودة.' : 'تم. التغيير في المسودة حتى تنشر.', $action === 'delete' ? null : $section];
        });
    }

    public function publish(LandingPage $page)
    {
        $this->builder->publish($page);

        return back()->with('success', 'نُشرت الصفحة، والزوار يرونها الآن على ' . $page->url());
    }

    public function discard(LandingPage $page)
    {
        return $this->run($page, function () use ($page) {
            $this->builder->discard($page);

            return ['رجعت الصفحة للنسخة المنشورة.', null];
        });
    }

    public function unpublish(LandingPage $page)
    {
        $page->update(['is_published' => false]);

        return back()->with('success', 'أُوقفت الصفحة؛ لن يراها الزوار حتى تنشرها مرة أخرى.');
    }

    public function destroy(LandingPage $page)
    {
        $page->delete();

        return redirect()->route('admin.landing.index')->with('success', 'حُذفت الصفحة.');
    }

    /** Runs a builder action; its message (and the section to scroll back to) or its error goes back to the builder. */
    private function run(LandingPage $page, callable $action)
    {
        try {
            [$message, $section] = $action();
        } catch (RuntimeException $e) {
            return redirect()->route('admin.landing.edit', $page)->withErrors($e->getMessage());
        }

        return redirect()->to(route('admin.landing.edit', $page) . ($section ? '#section-' . $section : ''))->with('success', $message);
    }
}
