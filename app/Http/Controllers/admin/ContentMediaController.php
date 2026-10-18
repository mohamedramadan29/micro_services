<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\ContentBlock;
use App\Models\ContentMedia;
use App\Services\Content\ContentAreas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/** «مكتبة الصور والخلفيات»: images and backgrounds used by managed content. */
class ContentMediaController extends Controller
{
    public const DIRECTORY = 'assets/uploads/content';

    public function index()
    {
        $media = ContentMedia::latest('id')->paginate(30);
        $used = $this->usedIds();

        return view('admin.content.media', compact('media', 'used'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'file' => 'required|image|mimes:jpg,jpeg,png,webp|max:4096',
            'alt' => 'nullable|string|max:255',
            'kind' => ['required', Rule::in(array_keys(ContentMedia::KINDS))],
        ], [
            'file.required' => 'اختر صورة لرفعها',
            'file.image' => 'الملف يجب أن يكون صورة',
            'file.mimes' => 'الصيغ المسموحة: jpg, png, webp',
            'file.max' => 'أقصى حجم للصورة 4 ميجابايت',
        ]);

        $file = $request->file('file');
        $name = Str::random(24) . '.' . strtolower($file->getClientOriginalExtension());
        [$width, $height] = @getimagesize($file->getRealPath()) ?: [null, null];
        $size = $file->getSize();
        $file->move(public_path(self::DIRECTORY), $name);

        ContentMedia::create([
            'path' => self::DIRECTORY . '/' . $name,
            'original_name' => Str::limit($file->getClientOriginalName(), 250, ''),
            'alt' => $data['alt'] ?? null,
            'kind' => $data['kind'],
            'width' => $width,
            'height' => $height,
            'size' => $size,
            'uploaded_by' => Auth::id(),
        ]);

        return back()->with('success', 'تم رفع الصورة، ويمكنك اختيارها الآن في محتوى الواجهة.');
    }

    /** The image description read by screen readers and search engines. */
    public function update(Request $request, ContentMedia $medium)
    {
        $data = $request->validate(['alt' => 'nullable|string|max:255']);
        $medium->update(['alt' => $data['alt'] ?? null]);

        return back()->with('success', 'تم حفظ وصف الصورة.');
    }

    public function destroy(ContentMedia $medium)
    {
        if (in_array($medium->id, $this->usedIds(), true)) {
            return back()->withErrors('هذه الصورة مستخدمة في محتوى الواجهة (منشور أو مسودة). استبدلها هناك أولًا.');
        }

        @unlink(public_path($medium->path));
        $medium->delete();

        return back()->with('success', 'تم حذف الصورة.');
    }

    /** Media ids referenced by an image/background field of any block, published or draft. */
    private function usedIds(): array
    {
        $areas = ContentAreas::all();

        $inBlocks = ContentBlock::all()
            ->flatMap(function (ContentBlock $block) use ($areas) {
                $keys = collect($areas[$block->area]['fields'] ?? [])->filter(fn ($f) => in_array($f['type'], ['image', 'background'], true))->keys();

                return $keys->flatMap(fn ($key) => [$block->data[$key] ?? null, $block->draft[$key] ?? null]);
            });
        // Landing pages: the OG image and every image/background field of their sections (published and draft).
        $inLanding = \App\Models\LandingPage::all()->flatMap(function ($page) {
            $ids = [$page->og_image_id];
            foreach ([(array) $page->content, (array) $page->draft] as $sections) {
                foreach ($sections as $section) {
                    foreach ((array) ($section['fields'] ?? []) as $key => $value) {
                        if (in_array($key, ['image', 'bg_image'], true)) {
                            $ids[] = $value;
                        }
                    }
                    foreach ((array) ($section['items'] ?? []) as $item) {
                        $ids[] = $item['image'] ?? null;
                    }
                }
            }

            return $ids;
        });

        return $inBlocks->merge($inLanding)->filter()->map(fn ($id) => (int) $id)->unique()->values()->all();
    }
}
