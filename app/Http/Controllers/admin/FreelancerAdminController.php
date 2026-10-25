<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\front\UserPortfolio;
use App\Models\User;
use App\Notifications\RequestUpdateNotification;
use App\Support\ImageVariants;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use RuntimeException;

/**
 * Freelancer applications (client file «تعديلات», items 03, 11, 12): the admin
 * checks the WhatsApp number (by opening a chat — no paid provider), adds a
 * professional photo, then approves; only approved freelancers are listed
 * and can send offers.
 */
class FreelancerAdminController extends Controller
{
    public const PHOTO_DIR = 'assets/uploads/freelancers';

    public function index(Request $request)
    {
        $status = (string) $request->string('status');
        $status = $status === 'ready' || array_key_exists($status, User::FREELANCER_STATUSES) ? $status : null;
        $freelancers = User::where('account_type', User::TYPE_FREELANCER)
            ->with('specializations:id,name')
            ->when($status === 'ready', fn ($q) => $q->readyForReview())
            ->when($status && $status !== 'ready', fn ($q) => $q->where('freelancer_status', $status))
            ->when($request->filled('q'), function ($query) use ($request) {
                $term = '%' . str_replace(['%', '_'], ['\%', '\_'], $request->query('q')) . '%';
                $query->where(fn ($q) => $q->where('name', 'like', $term)->orWhere('email', 'like', $term)->orWhere('whatsapp', 'like', $term)->orWhere('user_name', 'like', $term));
            })
            ->orderByRaw("CASE WHEN freelancer_status = 'pending' THEN 0 ELSE 1 END")
            ->orderByRaw('CASE WHEN freelancer_ready_at IS NULL THEN 1 ELSE 0 END')
            ->latest('id')
            ->paginate(25)
            ->withQueryString();
        $counts = User::where('account_type', User::TYPE_FREELANCER)->selectRaw('freelancer_status, COUNT(*) as total')->groupBy('freelancer_status')->pluck('total', 'freelancer_status');
        $counts['ready'] = User::readyForReview()->count();

        return view('admin.freelancers.index', compact('freelancers', 'status', 'counts'));
    }

    public function show(User $freelancer)
    {
        abort_unless($freelancer->isFreelancer(), 404);
        $freelancer->load('specializations:id,name');
        $portfolio = UserPortfolio::where('user_id', $freelancer->id)->latest('id')->limit(12)->get();

        return view('admin.freelancers.show', compact('freelancer', 'portfolio'));
    }

    /** The admin opened a WhatsApp chat with the number and got an answer. */
    /** The admin's manual verification (the alternative to a confirmed email). */
    public function verify(User $freelancer)
    {
        abort_unless($freelancer->isFreelancer(), 404);
        $freelancer->forceFill(['admin_verified_at' => $freelancer->admin_verified_at ? null : now()])->save();
        \Illuminate\Support\Facades\Cache::forget('admin-menu-badges');
        if (! $freelancer->admin_verified_at) {
            return back()->with('success', 'أُلغي التوثيق اليدوي.');
        }

        // Verifying is step 1 only: the freelancer is told, and the admin is reminded that approval is a separate step.
        $freelancer->notify(new RequestUpdateNotification(
            \App\Support\Msg::t('requests.freelancer_verified'),
            '/update-account#rf-freelancer',
            'request_updates'
        ));
        $freelancer->noteReadyForReview();

        return back()->with('success', $freelancer->isApprovedFreelancer()
            ? 'تم توثيق الحساب يدويًا وإبلاغ المستقل.'
            : 'تم توثيق الحساب وإبلاغ المستقل. حالته تبقى «قيد المراجعة» حتى تضغط «اعتماد» في خانة «3. القرار».');
    }

    public function resendVerification(User $freelancer)
    {
        abort_unless($freelancer->isFreelancer(), 404);
        if ($freelancer->email_verified_at) {
            return back()->with('success', 'البريد مؤكد بالفعل.');
        }
        \App\Support\EmailVerification::send($freelancer);

        return back()->with('success', 'أُرسل رابط تأكيد جديد إلى ' . $freelancer->email . '.');
    }

    public function photo(Request $request, User $freelancer)
    {
        abort_unless($freelancer->isFreelancer(), 404);
        $request->validate([
            'photo' => 'required|image|mimes:jpg,jpeg,png,webp|max:5120|dimensions:min_width=400,min_height=400',
        ], [
            'photo.required' => 'اختر الصورة',
            'photo.mimes' => 'الصيغ المسموحة: jpg, png, webp',
            'photo.max' => 'أقصى حجم 5 ميجابايت',
            'photo.dimensions' => 'الصورة صغيرة: أقل مقاس 400×400 بكسل حتى تظهر واضحة',
        ]);

        try {
            $base = ImageVariants::storeSquare($request->file('photo'), self::PHOTO_DIR);
        } catch (RuntimeException $e) {
            return back()->withErrors($e->getMessage());
        }
        ImageVariants::delete(self::PHOTO_DIR, $freelancer->professional_photo);
        $freelancer->forceFill(['professional_photo' => $base])->save();

        return back()->with('success', 'تم حفظ الصورة الاحترافية.');
    }

    public function decide(Request $request, User $freelancer)
    {
        abort_unless($freelancer->isFreelancer(), 404);
        $data = $request->validate([
            'decision' => ['required', Rule::in([User::FREELANCER_APPROVED, User::FREELANCER_REJECTED])],
            'note' => 'required_if:decision,' . User::FREELANCER_REJECTED . '|nullable|string|max:500',
        ], ['note.required_if' => 'اكتب سبب عدم الاعتماد ليعرف المستقل ما يعدّله']);

        if ($data['decision'] === User::FREELANCER_APPROVED && (! $freelancer->isVerifiedFreelancer() || ! $freelancer->professional_photo)) {
            return back()->withErrors('قبل الاعتماد: يجب أن يكون الحساب موثّقًا (بريد مؤكد أو توثيق يدوي) وأن تضيف الصورة الاحترافية.');
        }

        $freelancer->forceFill([
            'freelancer_status' => $data['decision'],
            'freelancer_review_note' => $data['note'] ?? null,
            'freelancer_reviewed_at' => now(),
            'freelancer_reviewed_by' => Auth::id(),
            'is_featured' => $data['decision'] === User::FREELANCER_APPROVED && $freelancer->is_featured,
        ])->save();

        $freelancer->notify(new RequestUpdateNotification(
            $data['decision'] === User::FREELANCER_APPROVED
                ? \App\Support\Msg::t('requests.freelancer_approved')
                : \App\Support\Msg::t('requests.freelancer_rejected', ['reason' => $data['note']]),
            '/update-account#rf-freelancer',
            'request_updates'
        ));

        return back()->with('success', $data['decision'] === User::FREELANCER_APPROVED ? 'تم اعتماد المستقل.' : 'تم تسجيل عدم الاعتماد وإبلاغ المستقل.');
    }

    public function feature(User $freelancer)
    {
        abort_unless($freelancer->isApprovedFreelancer(), 404);
        $freelancer->forceFill(['is_featured' => ! $freelancer->is_featured])->save();

        return back()->with('success', $freelancer->is_featured ? 'يظهر الآن ضمن «مستقلون موثوقون».' : 'أُزيل من «مستقلون موثوقون».');
    }
}
