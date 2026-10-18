<?php

namespace App\Http\Controllers\front;

use App\Http\Controllers\Controller;
use App\Models\SiteTest;
use App\Models\User;
use App\Services\SiteTests\SiteTestService;
use App\Services\SiteTests\SiteTestSettings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use RuntimeException;

/**
 * «اختبر موقعك مجاناً»: no login to check a site or see its summary (user
 * decision). A guest reaches their result with the token in the link (also
 * remembered in the session); creating the request after «yes» needs an account.
 */
class SiteTestController extends Controller
{
    private const SESSION_KEY = 'site_tests';

    public function __construct(private SiteTestService $tests)
    {
    }

    public function index()
    {
        abort_unless(SiteTestSettings::get('enabled'), 404);

        return view('website.site-tests.index', [
            'categories' => SiteTestSettings::categories(),
            'devices' => SiteTestSettings::devices(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'url' => 'required|string|max:2000',
            'categories' => 'required|array|min:1',
            'categories.*' => 'string',
            'device' => 'required|string',
        ], [
            'url.required' => 'اكتب رابط الموقع',
            'categories.required' => 'اختر مجالًا واحدًا على الأقل للفحص',
            'device.required' => 'اختر جهاز الفحص',
        ]);

        $key = 'site-test:' . $request->ip();
        $limit = max(1, (int) SiteTestSettings::get('hourly_limit'));
        if (RateLimiter::tooManyAttempts($key, $limit)) {
            return back()->withInput()->withErrors(['url' => 'وصلت للحد المسموح من الفحوصات. حاول مرة أخرى بعد ' . ceil(RateLimiter::availableIn($key) / 60) . ' دقيقة.']);
        }

        try {
            $parent = $request->filled('recheck') ? $this->accessible($request, SiteTest::where('number', $request->input('recheck'))->first()) : null;
            $test = $this->tests->start($request->input('url'), $request->input('device'), (array) $request->input('categories'), Auth::user(), $request->ip(), $parent);
        } catch (RuntimeException $e) {
            return back()->withInput()->withErrors(['url' => $e->getMessage()]);
        }
        RateLimiter::hit($key, 3600);
        $this->remember($test);

        return redirect()->route('site-tests.show', ['siteTest' => $test->number, 't' => $test->token]);
    }

    public function show(Request $request, SiteTest $siteTest)
    {
        $test = $this->accessible($request, $siteTest) ?? abort(404);
        $this->remember($test);
        $test->load(['issues', 'suggestedService', 'serviceRequest']);

        return view('website.site-tests.show', [
            'test' => $test,
            'maxIssues' => max(1, min(3, (int) SiteTestSettings::get('max_issues'))),
            'showOffer' => $test->suggested_sub_category_id
                && (SiteTestSettings::get('show_offer_immediately') || $test->review_status === SiteTest::REVIEW_SENT),
            'categories' => SiteTestSettings::CATEGORIES,
        ]);
    }

    /** Polled by the result page while the check runs. */
    public function status(Request $request, SiteTest $siteTest)
    {
        $test = $this->accessible($request, $siteTest) ?? abort(404);

        return response()->json(['status' => $test->status, 'finished' => $test->isFinished()] + $test->progress());
    }

    public function report(Request $request, SiteTest $siteTest)
    {
        $test = $this->accessible($request, $siteTest) ?? abort(404);
        $request->merge(['contact_phone' => \App\Support\Phone::normalize($request->input('contact_phone'))]);
        $data = $request->validate([
            'contact_name' => 'required|string|max:255',
            'contact_phone' => ['required', 'string', 'max:30', \App\Support\Phone::RULE],
            'contact_email' => 'nullable|email|max:255',
            'goal' => 'nullable|string|max:2000',
        ] + $this->questionRules(), [
            'answers.*.required' => 'أجب عن هذا السؤال',
            'answers.*.in' => 'اختر من القائمة',
            'contact_name.required' => 'اكتب اسمك',
            'contact_phone.required' => 'اكتب رقم الهاتف أو واتساب',
            'contact_phone.regex' => 'رقم الهاتف غير صحيح، اكتبه مع رمز الدولة',
            'contact_email.email' => 'البريد الإلكتروني غير صحيح',
        ]);

        try {
            $this->tests->requestReport($test, $data);
        } catch (RuntimeException $e) {
            return back()->withErrors($e->getMessage());
        }

        return $this->backToResult($test)->with('site_test_report', true);
    }

    public function choose(Request $request, SiteTest $siteTest, string $choice)
    {
        $test = $this->accessible($request, $siteTest) ?? abort(404);
        try {
            $this->tests->choose($test, $choice);
        } catch (RuntimeException $e) {
            return back()->withErrors($e->getMessage());
        }

        // «yes»: continue to the request (logging in first if needed).
        return $choice === 'yes'
            ? redirect()->route('site-tests.execute', ['siteTest' => $test->number, 't' => $test->token])
            : $this->backToResult($test)->with('site_test_declined', true);
    }

    /** Ask only for the execution data still missing, then publish the request. */
    public function execute(Request $request, SiteTest $siteTest)
    {
        $test = $this->accessible($request, $siteTest) ?? abort(404);
        if ($test->serviceRequest) {
            return $this->afterPublish($test);
        }
        abort_unless($test->choice === 'yes' && $test->suggestedService, 404);

        return view('website.site-tests.execute', ['test' => $test, 'contact' => $this->contactDefaults($test, Auth::user())]);
    }

    public function publish(Request $request, SiteTest $siteTest)
    {
        $test = $this->accessible($request, $siteTest) ?? abort(404);
        $defaults = $this->contactDefaults($test, Auth::user());
        $request->merge(['contact_phone' => \App\Support\Phone::normalize($request->input('contact_phone'))]);
        $data = $request->validate([
            'contact_name' => 'required|string|max:255',
            'contact_phone' => ['required', 'string', 'max:30', \App\Support\Phone::RULE],
            'contact_email' => 'required|email|max:255',
        ], [
            'contact_name.required' => 'اكتب اسمك',
            'contact_phone.required' => 'اكتب رقم التواصل',
            'contact_phone.regex' => 'رقم التواصل غير صحيح',
            'contact_email.required' => 'اكتب البريد الإلكتروني',
            'contact_email.email' => 'البريد الإلكتروني غير صحيح',
        ]) + $defaults;

        try {
            $this->tests->createRequest($test, Auth::user(), $data);
        } catch (RuntimeException $e) {
            return back()->withErrors($e->getMessage());
        }

        return $this->afterPublish($test->refresh());
    }

    /**
     * The visitor may open this check: the link's token, the session that
     * started it, its owner, or an admin. Null otherwise.
     */
    private function accessible(Request $request, ?SiteTest $test): ?SiteTest
    {
        if (! $test) {
            return null;
        }
        $user = Auth::user();
        $ok = hash_equals($test->token, (string) $request->query('t', $request->input('t', '')))
            || in_array($test->number, (array) session(self::SESSION_KEY, []), true)
            || ($user && (int) $test->user_id === (int) $user->id)
            || ($user && $user->account_type === User::TYPE_ADMIN);

        return $ok ? $test : null;
    }

    private function remember(SiteTest $test): void
    {
        $numbers = array_slice(array_unique(array_merge((array) session(self::SESSION_KEY, []), [$test->number])), -20);
        session([self::SESSION_KEY => $numbers]);
    }

    private function contactDefaults(SiteTest $test, User $user): array
    {
        return [
            'contact_name' => $test->contact_name ?: $user->name,
            'contact_phone' => $test->contact_phone ?: $user->phone,
            'contact_email' => $test->contact_email ?: $user->email,
        ];
    }

    private function backToResult(SiteTest $test)
    {
        return redirect()->to(route('site-tests.show', ['siteTest' => $test->number, 't' => $test->token]) . '#offer');
    }

    private function afterPublish(SiteTest $test)
    {
        return redirect()->route('requests.show', $test->serviceRequest)
            ->with('Success_message', 'تم نشر طلبك للمستقلين المختصين بخدمة ' . $test->serviceRequest->service_name . '، وستصلك عروضهم هنا.');
    }

    /** Validation of the admin-managed report questions (answers[<key>]). */
    private function questionRules(): array
    {
        $rules = [];
        foreach (SiteTestSettings::questions() as $question) {
            // A conditional question is required only when it is shown.
            $shown = SiteTestSettings::questionShown($question, (array) request()->input('answers', []));
            $rules['answers.' . $question['key']] = array_filter([
                ! empty($question['required']) && $shown ? 'required' : 'nullable',
                'string',
                $question['type'] === 'textarea' ? 'max:2000' : 'max:500',
                $question['type'] === 'select' ? \Illuminate\Validation\Rule::in($question['options']) : null,
            ]);
        }

        return $rules;
    }
}
