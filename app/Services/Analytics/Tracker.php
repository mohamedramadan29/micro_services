<?php

namespace App\Services\Analytics;

use App\Models\AnalyticsEvent;
use App\Models\AnalyticsVisit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

/**
 * First-party journey analytics (client file «تعديلات», item 08). Records
 * nothing until the visitor accepts (cookie `analytics_consent=1`); then an
 * anonymous visitor id (cookie) and a visit per 30 minutes of activity.
 * No name, email or phone is ever stored in an event.
 */
class Tracker
{
    public const CONSENT_COOKIE = 'analytics_consent';
    public const VISITOR_COOKIE = 'analytics_vid';
    public const SESSION_KEY = 'analytics_visit';
    public const ENTRY_KEY = 'analytics_entry';
    public const VISIT_GAP_MINUTES = 30;

    public const SOURCES = [
        'google' => 'Google',
        'facebook' => 'Facebook',
        'instagram' => 'Instagram',
        'youtube' => 'YouTube',
        'tiktok' => 'TikTok',
        'twitter' => 'X (تويتر)',
        'linkedin' => 'LinkedIn',
        'whatsapp' => 'واتساب',
        'email' => 'البريد',
        'search' => 'محركات بحث أخرى',
        'blog' => 'المدونة',
        'referral' => 'مواقع أخرى',
        'direct' => 'رابط مباشر',
    ];

    /** The order steps, in funnel order (the furthest one reached is kept on the visit). */
    public const FUNNEL = [
        'service_view' => 'زيارة الخدمة',
        'form_start' => 'بدء النموذج',
        'cart_add' => 'إضافة للسلة',
        'payment_start' => 'بدء الدفع',
        'order_complete' => 'إتمام الطلب',
    ];

    public const EVENTS = self::FUNNEL + [
        'page_view' => 'فتح صفحة',
        'request_submitted' => 'أرسل طلب خدمة',
        'payment_failed' => 'لم يكتمل الدفع',
        'abandon_reason' => 'سبب الترك',
        // Homepage entry points (client file «المربعات الخمسة»)
        'home_card_view' => 'ظهور مربعات الرئيسية',
        'home_card_click' => 'ضغط مربع في الرئيسية',
        'project_form_start' => 'بدء نموذج النشر المباشر',
        'project_published' => 'نشر مشروع مباشرة',
        'freelancer_profile_completed' => 'أكمل ملفه كمستقل',
        'support_requested' => 'طلب مساعدة',
        'diagnosis_completed' => 'أكمل التشخيص',
    ];

    /** Events that count as «completed» for a homepage card's visit. */
    public const CARD_GOALS = ['request_submitted', 'project_published', 'freelancer_profile_completed', 'support_requested', 'diagnosis_completed', 'order_complete'];

    /** Always the current request (the tracker never keeps one: tests and workers reuse the app). */
    private function request(): Request
    {
        return request();
    }

    public function consented(): bool
    {
        return $this->request()->cookie(self::CONSENT_COOKIE) === '1';
    }

    /** The current visit, created on the first tracked hit; null without consent or for bots. */
    public function visit(bool $create = true): ?AnalyticsVisit
    {
        $cached = $this->request()->attributes->get(self::SESSION_KEY);
        if ($cached || ! $this->request()->hasSession() || ! $this->consented() || $this->isBot()) {
            return $cached;
        }

        $id = (int) $this->request()->session()->get(self::SESSION_KEY);
        $visit = $id ? AnalyticsVisit::find($id) : null;
        $source = $this->source();
        $expired = $visit && $visit->last_seen_at->lt(now()->subMinutes(self::VISIT_GAP_MINUTES));
        $newCampaign = $visit && $this->request()->filled('utm_source') && $source['source'] !== $visit->source;

        if (! $visit || $expired || $newCampaign) {
            if (! $create) {
                return null;
            }
            // The first visit after consent keeps where the visitor really came in.
            $entry = $visit ? null : $this->request()->session()->pull(self::ENTRY_KEY);
            $useEntry = is_array($entry) && ! $this->request()->filled('utm_source');
            $visit = AnalyticsVisit::create(($useEntry ? array_intersect_key($entry, $source) : $source) + [
                'visitor_id' => $this->visitorId(),
                'user_id' => Auth::user()?->getKey(),
                'landing_path' => mb_substr($useEntry && isset($entry['landing_path']) ? $entry['landing_path'] : '/' . ltrim($this->request()->path(), '/'), 0, 500),
                'started_at' => now(),
                'last_seen_at' => now(),
            ]);
            $this->request()->session()->put(self::SESSION_KEY, $visit->id);
        }

        $this->request()->attributes->set(self::SESSION_KEY, $visit);

        return $visit;
    }

    /** Before consent: keep only the entry source in the session (nothing is stored in the database). */
    public function rememberEntry(): void
    {
        if ($this->request()->hasSession() && ! $this->request()->session()->has(self::ENTRY_KEY)
            && $this->request()->cookie(self::CONSENT_COOKIE) === null && ! $this->isBot()) {
            $this->request()->session()->put(self::ENTRY_KEY, $this->source() + ['landing_path' => mb_substr('/' . ltrim($this->request()->path(), '/'), 0, 500)]);
        }
    }

    /** Record a journey step on the current visit (no-op without consent). */
    public function record(string $type, array $attributes = []): ?AnalyticsEvent
    {
        $visit = $this->visit();

        return $visit ? self::recordOn($visit, $type, $attributes + ['path' => '/' . ltrim($this->request()->path(), '/')]) : null;
    }

    /** Record on a known visit (e.g. a payment confirmed later by the webhook). */
    public static function recordOn(AnalyticsVisit $visit, string $type, array $attributes = []): AnalyticsEvent
    {
        $event = $visit->events()->create([
            'type' => $type,
            'path' => isset($attributes['path']) ? mb_substr($attributes['path'], 0, 500) : null,
            'sub_category_id' => $attributes['sub_category_id'] ?? null,
            'meta' => $attributes['meta'] ?? null,
        ]);

        $steps = array_keys(self::FUNNEL);
        $reached = array_search($visit->last_step, $steps, true);
        $now = array_search($type, $steps, true);
        $visit->forceFill([
            'last_seen_at' => now(),
            'events_count' => $visit->events_count + 1,
            'user_id' => $visit->user_id ?? Auth::user()?->getKey(),
            'last_step' => $now !== false && ($reached === false || $now > $reached) ? $type : $visit->last_step,
        ])->save();

        return $event;
    }

    /** Where the visit came from: UTM, then ad click ids, then the referrer; blog landings count for the blog. */
    public function source(): array
    {
        $r = $this->request();
        $refHost = strtolower((string) parse_url((string) $r->headers->get('referer'), PHP_URL_HOST));
        $ownHost = strtolower($r->getHost());
        $refHost = $refHost === $ownHost ? '' : preg_replace('/^www\./', '', $refHost);

        $utm = strtolower((string) $r->query('utm_source'));
        $source = match (true) {
            $utm !== '' => $this->platform($utm) ?? 'referral',
            $r->filled('gclid') => 'google',
            $r->filled('fbclid') => 'facebook',
            default => null,
        };
        if (! $source) {
            $platform = $refHost ? $this->platform($refHost) : null;
            $landingBlog = str_starts_with(trim(preg_replace('#^(ar|en)(/|$)#', '', $r->path()), '/'), 'blog');
            $source = match (true) {
                $landingBlog && (! $platform || in_array($platform, ['google', 'search'], true)) => 'blog',
                (bool) $platform => $platform,
                $refHost !== '' => 'referral',
                default => 'direct',
            };
        }

        return [
            'source' => $source,
            'medium' => mb_substr((string) ($r->query('utm_medium') ?: ($r->filled('gclid') ? 'cpc' : '')), 0, 60) ?: null,
            'campaign' => mb_substr((string) $r->query('utm_campaign'), 0, 120) ?: null,
            'referrer_host' => $refHost ?: null,
        ];
    }

    private function platform(string $value): ?string
    {
        return match (true) {
            (bool) preg_match('/(^|\.)google\.|^google$/', $value) => 'google',
            (bool) preg_match('/facebook|^fb$|fb\.com|fb\.me/', $value) => 'facebook',
            (bool) preg_match('/instagram|^ig$/', $value) => 'instagram',
            (bool) preg_match('/youtube|youtu\.be|^yt$/', $value) => 'youtube',
            (bool) preg_match('/tiktok/', $value) => 'tiktok',
            (bool) preg_match('/twitter|^t\.co$|^x\.com$|^x$/', $value) => 'twitter',
            (bool) preg_match('/linkedin|lnkd\.in/', $value) => 'linkedin',
            (bool) preg_match('/whatsapp|wa\.me/', $value) => 'whatsapp',
            (bool) preg_match('/mail|newsletter/', $value) => 'email',
            (bool) preg_match('/bing\.|yahoo\.|duckduckgo|yandex\./', $value) => 'search',
            default => null,
        };
    }

    private function visitorId(): string
    {
        $id = (string) $this->request()->cookie(self::VISITOR_COOKIE);
        if (! preg_match('/^[a-f0-9-]{36}$/', $id)) {
            $id = (string) Str::uuid();
            cookie()->queue(cookie()->forever(self::VISITOR_COOKIE, $id));
        }

        return $id;
    }

    private function isBot(): bool
    {
        return (bool) preg_match('/bot|crawl|spider|slurp|preview|facebookexternalhit|whatsapp|curl|wget|python|headless/i', (string) $this->request()->userAgent());
    }
}
