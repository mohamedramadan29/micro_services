<?php

namespace App\Services\SiteTests;

use App\Jobs\RunSiteTest;
use App\Models\admin\SubCategory;
use App\Models\ServiceRequest;
use App\Models\SiteTest;
use App\Models\SiteTestRule;
use App\Models\User;
use App\Notifications\SiteTestNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * The free site check: start → run the engine (queued job) → issues with
 * evidence mapped to services by the admin's rules → optional full report
 * → the client's yes (one classified request, published to freelancers)
 * or no (saved only). Every step can be retried without duplicates.
 */
class SiteTestService
{
    public function __construct(private SiteTestEngine $engine, private SiteUrlGuard $guard)
    {
    }

    public function start(string $url, string $device, array $categories, ?User $user, ?string $ip, ?SiteTest $parent = null): SiteTest
    {
        if (! SiteTestSettings::get('enabled')) {
            throw new RuntimeException('خدمة الفحص متوقفة مؤقتًا.');
        }
        $url = $this->guard->check($url);
        $categories = array_values(array_intersect(array_keys(SiteTestSettings::categories()), $categories));
        if (! $categories) {
            throw new RuntimeException('اختر مجالًا واحدًا على الأقل للفحص.');
        }
        if (! array_key_exists($device, SiteTestSettings::devices())) {
            throw new RuntimeException('اختر جهاز الفحص.');
        }

        $test = SiteTest::create([
            'token' => Str::random(40),
            'user_id' => $user?->id,
            'parent_id' => $parent?->id,
            'url' => $url,
            'host' => parse_url($url, PHP_URL_HOST),
            'device' => $device,
            'categories' => $categories,
            'status' => SiteTest::STATUS_QUEUED,
            'ip' => $ip,
        ]);
        $test->update(['number' => 'SITE-' . (1000 + $test->id)]);

        RunSiteTest::dispatch($test->id);

        return $test;
    }

    /** Run the engine for each device and store scores, raw answers and issues. */
    public function run(SiteTest $test): void
    {
        if ($test->isFinished()) {
            return;
        }
        $test->update(['status' => SiteTest::STATUS_RUNNING, 'engine' => $this->engine->name()]);

        $scores = [];
        $issues = [];
        $errors = [];
        foreach ($test->devices() as $device) {
            try {
                $result = $this->engine->run($test->url, $device, $test->categories);
                $test->results()->create(['device' => $device, 'engine' => $this->engine->name(), 'ok' => true, 'raw' => $result['raw'] ?? null, 'fetched_at' => now()]);
                $scores[$device] = $result['scores'];
                foreach ($result['issues'] as $issue) {
                    $issues[] = $issue + ['device' => $device];
                }
            } catch (\Throwable $e) {
                Log::warning('Site test engine failed', ['test' => $test->id, 'device' => $device, 'error' => $e->getMessage()]);
                $test->results()->create(['device' => $device, 'engine' => $this->engine->name(), 'ok' => false, 'error' => mb_substr($e->getMessage(), 0, 250), 'fetched_at' => now()]);
                $errors[] = $e->getMessage();
            }
        }

        if (! $scores) {
            // A failed check is never shown as a report (spec page 15).
            $test->update(['status' => SiteTest::STATUS_FAILED, 'failure_reason' => SiteTest::friendlyFailure($errors[0] ?? null), 'checked_at' => now()]);

            return;
        }

        DB::transaction(function () use ($test, $scores, $issues) {
            $this->storeIssues($test, $issues);
            $first = $test->issues()->where('shown', true)->whereNotNull('sub_category_id')->get()
                ->sortBy(fn ($i) => [\App\Models\SiteTestIssue::PRIORITY_ORDER[$i->priority] ?? 9, $i->sort_order])->first();
            $test->update([
                'status' => SiteTest::STATUS_DONE,
                'scores' => $scores,
                'checked_at' => now(),
                'suggested_sub_category_id' => $first?->sub_category_id,
            ]);
        });
        // Issues but no rule linking them to a service: the team picks one (spec page 16 #5).
        if (! $test->suggested_sub_category_id && $test->issues()->where('shown', true)->where('source', 'tool')->exists()) {
            Notification::send(User::activeAdmins()->get(), new SiteTestNotification($test->fresh(), 'norule'));
        }
    }

    /** The client asks for the full report and a specialist's review (the only step that asks for contact data). */
    public function requestReport(SiteTest $test, array $contact): void
    {
        if ($test->status !== SiteTest::STATUS_DONE) {
            throw new RuntimeException('انتظر حتى يكتمل الفحص.');
        }
        if ($test->review_status !== SiteTest::REVIEW_NONE) {
            return;
        }

        // Answers to the admin's questions, kept with the label shown at the time.
        $given = (array) ($contact['answers'] ?? []);
        if (! isset($given['goal']) && ! empty($contact['goal'])) {
            $given['goal'] = $contact['goal']; // older form field
        }
        $answers = [];
        foreach (SiteTestSettings::questions() as $question) {
            if (! SiteTestSettings::questionShown($question, $given)) {
                continue; // hidden by its condition: not saved
            }
            $value = trim((string) ($given[$question['key']] ?? ''));
            if ($value !== '') {
                $answers[] = ['key' => $question['key'], 'label' => $question['label'], 'value' => $value];
            }
        }

        $test->update([
            'contact_name' => $contact['contact_name'] ?? null,
            'contact_phone' => $contact['contact_phone'],
            'contact_email' => $contact['contact_email'] ?? null,
            'goal' => $given['goal'] ?? null,
            'answers' => $answers ?: null,
            'report_requested_at' => now(),
            'review_status' => SiteTest::REVIEW_REQUESTED,
        ]);
        Notification::send(User::activeAdmins()->get(), new SiteTestNotification($test, 'report'));
    }

    /**
     * The client's answer to the offer card. «no» is saved and the team told
     * (red). «yes» is saved and told (green); the request itself is created by
     * createRequest() once the client is logged in.
     */
    public function choose(SiteTest $test, string $choice): void
    {
        if (! in_array($choice, ['yes', 'no'], true) || $test->status !== SiteTest::STATUS_DONE || ! $test->suggested_sub_category_id) {
            throw new RuntimeException('لا يوجد عرض متاح لهذا الفحص.');
        }

        $changed = DB::transaction(function () use ($test, $choice) {
            $locked = SiteTest::whereKey($test->id)->lockForUpdate()->first();
            if ($locked->service_request_id || $locked->choice === $choice) {
                return false; // already answered the same way, or already published
            }
            $locked->update(['choice' => $choice, 'choice_at' => now()]);

            return true;
        });

        if ($changed) {
            $test->refresh();
            Notification::send(User::activeAdmins()->get(), new SiteTestNotification($test, $choice));
        }
    }

    /**
     * «yes»: one classified request, linked to the check, published directly
     * to the freelancers of the service. Running it twice never makes two.
     */
    public function createRequest(SiteTest $test, User $client, array $contact): ServiceRequest
    {
        if (! $client->isClient()) {
            throw new RuntimeException('طلب التنفيذ متاح لحسابات العملاء فقط.');
        }
        $service = SubCategory::with('category')->find($test->suggested_sub_category_id);
        if ($test->choice !== 'yes' || ! $service) {
            throw new RuntimeException('لا يوجد عرض مقبول لهذا الفحص.');
        }

        $request = DB::transaction(function () use ($test, $client, $contact, $service) {
            $locked = SiteTest::whereKey($test->id)->lockForUpdate()->first();
            if ($locked->service_request_id) {
                return ServiceRequest::findOrFail($locked->service_request_id);
            }

            $request = ServiceRequest::create([
                'source' => ServiceRequest::SOURCE_SITE_TEST,
                'user_id' => $client->id,
                'category_id' => $service->category?->id,
                'sub_category_id' => $service->id,
                'category_name' => $service->category?->name,
                'service_name' => $service->name,
                'path' => ServiceRequest::PATH_EXECUTE,
                'status' => ServiceRequest::STATUS_OPEN,
                'problem' => $this->problemText($locked),
                'contact_name' => $contact['contact_name'],
                'contact_phone' => $contact['contact_phone'],
                'contact_email' => $contact['contact_email'],
                'published_at' => now(),
                'site_test_id' => $locked->id,
            ]);
            $request->update(['number' => 'REQ-' . (1000 + $request->id)]);
            $locked->update(['service_request_id' => $request->id, 'user_id' => $locked->user_id ?? $client->id]);

            return $request;
        });

        if ($test->refresh()->service_request_id === $request->id && $request->wasRecentlyCreated) {
            Notification::send(User::activeAdmins()->get(), new SiteTestNotification($test, 'published'));
        }

        return $request;
    }

    /** What freelancers read: the site and the issues with evidence, no contact data. */
    public function problemText(SiteTest $test): string
    {
        $lines = ['طلب من فحص الموقع ' . $test->number . ' للرابط: ' . $test->url];
        foreach ($test->clientIssues((int) SiteTestSettings::get('max_issues') ?: 3) as $issue) {
            $lines[] = '- ' . $issue->title . ($issue->evidence ? ' (' . $issue->evidence . ')' : '');
        }
        foreach ((array) $test->answers as $answer) {
            $lines[] = $answer['label'] . ': ' . $answer['value'];
        }
        if (! $test->answers && $test->goal) {
            $lines[] = 'هدف العميل: ' . $test->goal;
        }

        return implode("\n", $lines);
    }

    private function storeIssues(SiteTest $test, array $issues): void
    {
        $rules = SiteTestRule::where('active', true)->get()->keyBy('audit_code');

        // One row per audit (worst score across devices), most important first.
        $byCode = collect($issues)->filter(fn ($i) => $i['code'] !== '')
            ->groupBy('code')
            ->map(fn ($group) => $group->sortBy('score')->first())
            ->sortBy(fn ($i) => [$i['score'] < 0.5 ? 0 : 1, -$i['weight'], $i['score']])
            ->values();

        $order = 0;
        foreach ($byCode as $issue) {
            $rule = $rules->get($issue['code']);
            $test->issues()->create([
                'source' => 'tool',
                'audit_code' => $issue['code'],
                'category' => $issue['category'],
                'device' => $issue['device'],
                'priority' => $rule?->priority ?? ($issue['score'] < 0.5 ? 'high' : 'medium'),
                'title' => $issue['title'] ?: $issue['code'],
                'description' => $issue['description'],
                'evidence' => $issue['evidence'],
                'page_url' => $test->url,
                'sub_category_id' => $rule?->sub_category_id,
                'shown' => true,
                'sort_order' => ++$order,
            ]);
        }
    }
}
