<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Http\Traits\Upload_Images;
use App\Jobs\PublishSocialPostJob;
use App\Models\SocialAccount;
use App\Models\SocialPost;
use App\Models\SocialPostResult;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class SocialPostController extends Controller
{
    use Upload_Images;
    /** صفحة إنشاء بوست جديد */
    public function create()
    {
        $accounts = SocialAccount::active()->get()->groupBy('platform');
        return view('admin.social-media.create', compact('accounts'));
    }

    /** حفظ وإرسال البوست */
    public function store(Request $request)
    {
        $request->validate([
            'content'    => 'required|string|max:5000',
            'platforms'  => 'required|array|min:1',
            'platforms.*'=> 'in:facebook,instagram,tiktok,youtube,twitter,linkedin',
            'media_type' => 'required|in:text,image,video,reel,story',
            'media'      => 'nullable|array',
            'media.*'    => 'file|max:512000', // 500MB
            'action'     => 'required|in:publish,schedule,draft',
            'scheduled_at'=> 'required_if:action,schedule|nullable|date|after:now',
        ], [
            'content.required'    => 'محتوى البوست مطلوب',
            'platforms.required'  => 'يجب اختيار منصة واحدة على الأقل',
            'scheduled_at.after'  => 'وقت الجدولة يجب أن يكون في المستقبل',
        ]);

        // رفع الميديا
        $mediaPaths = [];
        if ($request->hasFile('media')) {
            foreach ($request->file('media') as $file) {
                // استخدام الـ Trait الخاص بك
                $fileName = $this->saveImage($file, 'assets/uploads/social');
                $mediaPaths[] = 'assets/uploads/social/' . $fileName;
            }
        }

        // تحديد الحالة
        $status = match($request->action) {
            'publish'  => 'draft',     // سيتغير عند النشر
            'schedule' => 'scheduled',
            'draft'    => 'draft',
        };

        $post = SocialPost::create([
            'title'        => $request->title,
            'content'      => $request->content,
            'media_type'   => $request->media_type,
            'media_paths'  => empty($mediaPaths) ? null : $mediaPaths,
            'platforms'    => $request->platforms,
            'status'       => $status,
            'scheduled_at' => $request->action === 'schedule' ? $request->scheduled_at : null,
            'created_by'   => Auth::id(),
        ]);

        // النشر الفوري
        if ($request->action === 'publish') {
            PublishSocialPostJob::dispatch($post);
            return redirect()->route('admin.social.index')
                ->with('success', 'تم إرسال البوست للنشر! ستظهر النتائج خلال لحظات.');
        }

        // جدولة
        if ($request->action === 'schedule') {
            PublishSocialPostJob::dispatch($post)->delay($post->scheduled_at);
            return redirect()->route('admin.social.scheduled')
                ->with('success', 'تم جدولة البوست بنجاح في ' . $post->scheduled_at->format('Y-m-d H:i'));
        }

        return redirect()->route('admin.social.index')
            ->with('success', 'تم حفظ المسودة بنجاح');
    }

    /** عرض البوستات المجدولة */
    public function scheduled()
    {
        $posts = SocialPost::where('status', 'scheduled')
            ->with('results')
            ->latest('scheduled_at')
            ->paginate(15);
        return view('admin.social-media.scheduled', compact('posts'));
    }

    /** عرض البوستات المنشورة */
    public function published()
    {
        $posts = SocialPost::whereIn('status', ['published', 'partial', 'failed'])
            ->with('results.account')
            ->latest('published_at')
            ->paginate(15);
        return view('admin.social-media.published', compact('posts'));
    }

    /** تفاصيل بوست */
    public function show(SocialPost $post)
    {
        $post->load('results.account', 'creator');
        return view('admin.social-media.show', compact('post'));
    }

    /** حذف بوست */
    public function destroy(SocialPost $post)
    {
        // حذف الميديا من الـ storage
        if ($post->media_paths) {
            foreach ($post->media_paths as $path) {
                Storage::disk('public')->delete($path);
            }
        }
        $post->delete();
        return back()->with('success', 'تم حذف البوست بنجاح');
    }

    /** إعادة نشر بوست فاشل */
    public function retry(SocialPost $post)
    {
        if (!in_array($post->status, ['failed', 'partial'])) {
            return back()->with('error', 'لا يمكن إعادة نشر هذا البوست');
        }

        $post->update(['status' => 'draft']);
        $post->results()->delete();
        PublishSocialPostJob::dispatch($post);

        return back()->with('success', 'تم إعادة إرسال البوست للنشر');
    }
}
