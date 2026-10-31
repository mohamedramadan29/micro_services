<?php

namespace App\Http\Controllers\front;

use App\Http\Controllers\Controller;
use App\Models\Suggestion;
use App\Models\SuggestionCategory;
use App\Services\Suggestions\SuggestionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * «شاركنا اقتراحك» (client file «نظام الاقتراحات»): open to visitors (user decision 2026-10-31);
 * an account's identity and type are taken from the account. «اقتراحاتي» lists the account's own only.
 */
class SuggestionController extends Controller
{
    public const SENT_PER_HOUR = 5;

    public function create()
    {
        return view('website.suggestions.create', [
            'categories' => SuggestionCategory::active()->get(),
            'submissionKey' => (string) Str::uuid(),
            'user' => Auth::user(),
        ]);
    }

    public function store(Request $request, SuggestionService $service)
    {
        // Honeypot for bots (a hidden field people never fill).
        if (filled($request->input('nf_extra'))) {
            return back()->withInput()->withErrors(['title' => 'تعذّر إرسال الاقتراح، حاول مرة أخرى.']);
        }

        $guest = ! Auth::check();
        $data = $request->validate([
            'submission_key' => 'required|uuid',
            'category_id' => ['required', 'integer', Rule::exists('suggestion_categories', 'id')->where('is_active', true)],
            'title' => 'required|string|min:5|max:' . Suggestion::TITLE_MAX,
            'description' => 'required|string|min:' . Suggestion::DESCRIPTION_MIN . '|max:' . Suggestion::DESCRIPTION_MAX,
            'guest_name' => $guest ? 'nullable|string|max:100' : 'prohibited',
            'wants_updates' => $guest ? 'nullable|boolean' : 'prohibited',
            'guest_email' => $guest ? 'nullable|required_if_accepted:wants_updates|email:rfc|max:150' : 'prohibited',
        ], [
            'submission_key.*' => 'انتهت صلاحية النموذج، أعد تحميل الصفحة ثم أرسل (ما كتبته محفوظ).',
            'category_id.required' => 'اختر نوع الاقتراح',
            'category_id.*' => 'نوع الاقتراح المختار غير متاح',
            'title.required' => 'اكتب عنوانًا واضحًا للاقتراح',
            'title.min' => 'العنوان قصير جدًا',
            'title.max' => 'العنوان أطول من المسموح (' . Suggestion::TITLE_MAX . ' حرفًا)',
            'description.required' => 'اشرح لنا فكرتك',
            'description.min' => 'اكتب شرحًا أوضح قليلًا (' . Suggestion::DESCRIPTION_MIN . ' حرفًا على الأقل)',
            'description.max' => 'الشرح أطول من المسموح (' . Suggestion::DESCRIPTION_MAX . ' حرف)',
            'guest_name.max' => 'الاسم أطول من المسموح',
            'guest_email.required_if_accepted' => 'اكتب بريدك لنرسل لك التحديثات، أو ألغِ اختيار التحديثات',
            'guest_email.email' => 'البريد الإلكتروني غير صحيح',
            'guest_email.max' => 'البريد الإلكتروني أطول من المسموح',
        ]);
        $data['title'] = trim($data['title']);
        $data['description'] = trim($data['description']);

        // A resend of the same form (double click, network error) is not a new suggestion.
        if ($suggestion = $service->alreadySent($data['submission_key'], Auth::user(), $request->ip())) {
            return redirect()->route('suggestions.create')->with('suggestion_created', $suggestion->reference_code);
        }

        // At most SENT_PER_HOUR saved suggestions per account, or per browser session for a visitor (so an
        // office or proxy behind one IP is not blocked by its first few senders). Only SAVED ones count, so a
        // visitor who fixes a few typos is never blocked. Bots without cookies are capped by the route's
        // per-IP throttle (every attempt counts there).
        $limitKey = 'suggestions:' . (Auth::check() ? 'user:' . Auth::id() : 'session:' . $request->session()->getId());
        if (RateLimiter::tooManyAttempts($limitKey, self::SENT_PER_HOUR)) {
            $minutes = (int) ceil(RateLimiter::availableIn($limitKey) / 60);

            return back()->withInput()->withErrors(['title' => "أرسلت عدة اقتراحات خلال وقت قصير. شكرًا لحماسك! حاول مرة أخرى بعد {$minutes} دقيقة (ما كتبته محفوظ)."]);
        }

        [$suggestion, $created] = $service->submit($data, Auth::user(), $request->ip());
        if ($created) {
            RateLimiter::hit($limitKey, 3600);
        }

        // The confirmation is shown only after the suggestion is saved.
        return redirect()->route('suggestions.create')->with('suggestion_created', $suggestion->reference_code);
    }

    public function mine(Request $request)
    {
        $suggestions = Suggestion::with('category')->where('user_id', Auth::id())->latest('id')->paginate(15);

        return view('website.suggestions.mine', compact('suggestions'));
    }

    public function show(string $code)
    {
        // Loaded with the owner in the query: another account's code is simply not found.
        $suggestion = Suggestion::with('category')->where('user_id', Auth::id())->where('reference_code', $code)->firstOrFail();

        return view('website.suggestions.show', compact('suggestion'));
    }
}
