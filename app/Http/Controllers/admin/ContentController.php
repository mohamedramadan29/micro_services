<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\ContentBlock;
use App\Models\ContentMedia;
use App\Services\Content\ContentAreas;
use App\Services\Content\ContentManager;
use Illuminate\Http\Request;
use RuntimeException;

/** «إدارة محتوى الواجهة» (client file «تعديلات», item 06). */
class ContentController extends Controller
{
    public function __construct(private ContentManager $content)
    {
    }

    public function index(Request $request)
    {
        $page = array_key_exists((string) $request->string('page'), ContentAreas::PAGES) ? (string) $request->string('page') : array_key_first(ContentAreas::PAGES);
        $areas = collect(ContentAreas::PAGES[$page]['areas'])
            ->mapWithKeys(fn ($area) => [$area => ['definition' => ContentAreas::get($area), 'blocks' => $this->content->blocks($area)]]);
        $hasDrafts = collect(ContentAreas::PAGES[$page]['areas'])->contains(fn ($area) => $this->content->hasDrafts($area));
        $media = ContentMedia::latest('id')->get();

        $access = $this->access();
        $revisions = \App\Models\ContentRevision::with('user')->where('page', $page)->latest('id')->limit(15)->get();

        return view('admin.content.index', compact('page', 'areas', 'hasDrafts', 'media', 'access', 'revisions'));
    }

    public function update(Request $request, ContentBlock $block)
    {
        $this->need('edit');
        $input = $request->except('_token');
        // A content editor changes texts; destinations, groups and audiences stay as the manager set them.
        if ($this->access() !== 'publish') {
            foreach (ContentAreas::get($block->area)['sensitive'] ?? [] as $key) {
                $input[$key] = $block->working()[$key] ?? null;
            }
        }
        $this->content->saveDraft($block, $input);

        return $this->back($block, 'حُفظ التعديل كمسودة. اضغط «نشر» ليظهر للزوار.');
    }

    public function store(Request $request, string $area)
    {
        abort_unless(array_key_exists($area, ContentAreas::all()), 404);
        $this->need(! empty(ContentAreas::get($area)['sensitive']) ? 'publish' : 'edit');

        return $this->attempt(fn () => $this->content->add($area), $area, 'أُضيف عنصر جديد كمسودة. املأ بياناته ثم انشر.');
    }

    public function destroy(ContentBlock $block)
    {
        $this->need(! empty(ContentAreas::get($block->area)['sensitive']) ? 'publish' : 'edit');
        return $this->attempt(fn () => $this->content->remove($block), $block->area, 'سيُحذف العنصر عند النشر.');
    }

    public function toggle(ContentBlock $block)
    {
        $this->need('edit');
        $this->content->toggle($block);

        return $this->back($block, 'تم تغيير الظهور في المسودة.');
    }

    public function move(ContentBlock $block, string $direction)
    {
        $this->need('edit');
        $this->content->move($block, $direction === 'up' ? -1 : 1);

        return $this->back($block, 'تم تغيير الترتيب في المسودة.');
    }

    public function publish(string $page)
    {
        abort_unless(array_key_exists($page, ContentAreas::PAGES), 404);
        $this->need('publish');
        $count = $this->content->publish(ContentAreas::PAGES[$page]['areas']);
        if ($count) {
            $this->content->record($page);
        }

        return redirect()->route('admin.content.index', ['page' => $page])
            ->with('success', $count ? 'تم النشر، والتعديلات ظاهرة الآن للزوار.' : 'لا توجد تعديلات غير منشورة.');
    }

    public function discard(string $page)
    {
        abort_unless(array_key_exists($page, ContentAreas::PAGES), 404);
        $this->need('edit');
        $this->content->discard(ContentAreas::PAGES[$page]['areas']);

        return redirect()->route('admin.content.index', ['page' => $page])->with('success', 'تم تجاهل التعديلات غير المنشورة.');
    }

    /** Put a page back as it was after an earlier publish (manager only). */
    public function rollback(\App\Models\ContentRevision $revision)
    {
        $this->need('publish');
        $this->content->rollback($revision);

        return redirect()->route('admin.content.index', ['page' => $revision->page])
            ->with('success', 'أُعيد القسم كما كان في ' . $revision->created_at->format('Y-m-d H:i') . ' ونُشر للزوار.');
    }

    private function access(): string
    {
        return \App\Support\StaffPermissions::contentAccess(auth()->user());
    }

    /** 'edit' or 'publish' (see StaffPermissions::contentAccess). */
    private function need(string $level): void
    {
        $access = $this->access();
        abort_unless($access === 'publish' || ($level === 'edit' && $access === 'edit'), 403,
            $level === 'publish' ? 'النشر وتغيير الوجهات للمدير فقط' : 'دورك يسمح بالاطلاع فقط');
    }

    /** Open the site showing the drafts (only for this admin's session). */
    public function preview(string $page)
    {
        abort_unless(array_key_exists($page, ContentAreas::PAGES), 404);
        session([ContentManager::PREVIEW_SESSION => true]);

        return redirect(ContentAreas::PAGES[$page]['preview']);
    }

    public function stopPreview()
    {
        session()->forget(ContentManager::PREVIEW_SESSION);

        return back();
    }

    private function attempt(callable $action, string $area, string $success)
    {
        try {
            $action();
        } catch (RuntimeException $e) {
            return redirect()->route('admin.content.index', ['page' => $this->pageOf($area)])->withErrors($e->getMessage());
        }

        return redirect()->route('admin.content.index', ['page' => $this->pageOf($area)])->with('success', $success);
    }

    private function back(ContentBlock $block, string $message)
    {
        return redirect()->to(route('admin.content.index', ['page' => $this->pageOf($block->area)]) . '#block-' . $block->id)->with('success', $message);
    }

    private function pageOf(string $area): string
    {
        return collect(ContentAreas::PAGES)->search(fn ($page) => in_array($area, $page['areas'], true)) ?: array_key_first(ContentAreas::PAGES);
    }
}
