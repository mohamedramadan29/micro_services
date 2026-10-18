<?php

namespace App\Services\SiteTests;

use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Google PageSpeed Insights API v5 (Lighthouse run by Google, so our server
 * never fetches the client's site). Arabic texts via locale=ar.
 * «استخدام الهاتف» has no category of its own: it is scored from the mobile
 * usability audits Lighthouse still reports.
 */
class PageSpeedEngine implements SiteTestEngine
{
    public const ENDPOINT = 'https://www.googleapis.com/pagespeedonline/v5/runPagespeed';

    /** Lighthouse categories we ask for (our key => Lighthouse id). */
    private const LIGHTHOUSE = ['performance' => 'performance', 'seo' => 'seo', 'accessibility' => 'accessibility', 'best-practices' => 'best-practices'];

    public const MOBILE_AUDITS = ['viewport', 'font-size', 'tap-targets', 'content-width'];

    public function name(): string
    {
        return 'PageSpeed Insights';
    }

    public function run(string $url, string $device, array $categories): array
    {
        $key = config('services.pagespeed.key');
        if (! $key) {
            throw new RuntimeException('مفتاح PageSpeed غير مضبوط');
        }

        $asked = array_values(array_intersect_key(self::LIGHTHOUSE, array_flip($categories)));
        if (in_array('mobile', $categories, true) && ! in_array('seo', $asked, true)) {
            $asked[] = 'seo'; // the mobile usability audits live in the SEO category
        }
        $query = http_build_query(['url' => $url, 'strategy' => $device, 'locale' => 'ar', 'key' => $key]);
        foreach ($asked as $category) {
            $query .= '&category=' . urlencode($category);
        }

        $response = Http::timeout(90)->get(self::ENDPOINT . '?' . $query);
        if (! $response->successful()) {
            $message = (string) $response->json('error.message', 'HTTP ' . $response->status());
            throw new RuntimeException('PageSpeed: ' . mb_substr($message, 0, 200));
        }

        return $this->parse((array) $response->json(), $categories) + ['raw' => $response->body()];
    }

    /** Scores and failed audits from a PageSpeed answer. */
    public function parse(array $data, array $categories): array
    {
        $lighthouse = $data['lighthouseResult'] ?? [];
        $audits = $lighthouse['audits'] ?? [];
        $scores = [];
        $issues = [];

        foreach ($categories as $category) {
            if ($category === 'mobile') {
                $values = array_filter(array_map(fn ($id) => $audits[$id]['score'] ?? null, self::MOBILE_AUDITS), fn ($v) => $v !== null);
                $scores['mobile'] = $values ? (int) round(array_sum($values) / count($values) * 100) : null;
                foreach (self::MOBILE_AUDITS as $id) {
                    if (isset($audits[$id])) {
                        $issues[] = $this->issue($audits[$id], 'mobile', 1.0);
                    }
                }
                continue;
            }

            $node = $lighthouse['categories'][self::LIGHTHOUSE[$category] ?? ''] ?? null;
            $scores[$category] = isset($node['score']) ? (int) round($node['score'] * 100) : null;
            foreach ($node['auditRefs'] ?? [] as $ref) {
                if (isset($audits[$ref['id']]) && ($ref['group'] ?? '') !== 'hidden') {
                    $issues[] = $this->issue($audits[$ref['id']], $category, (float) ($ref['weight'] ?? 0));
                }
            }
        }

        return ['scores' => $scores, 'issues' => array_values(array_filter($issues))];
    }

    /** A failing, measurable audit as an issue; null when it passed or is informative only. */
    private function issue(array $audit, string $category, float $weight): ?array
    {
        $score = $audit['score'] ?? null;
        $mode = $audit['scoreDisplayMode'] ?? '';
        if ($score === null || $score >= 0.9 || ! in_array($mode, ['binary', 'numeric', 'metricSavings'], true)) {
            return null;
        }

        return [
            'code' => (string) ($audit['id'] ?? ''),
            'category' => $category,
            'title' => mb_substr(strip_tags((string) ($audit['title'] ?? '')), 0, 250),
            // "[Learn more](https://…)" → "Learn more"
            'description' => mb_substr(trim(preg_replace('/\[([^\]]+)\]\([^)]+\)/u', '$1', (string) ($audit['description'] ?? ''))), 0, 1000) ?: null,
            'evidence' => isset($audit['displayValue']) ? mb_substr(strip_tags((string) $audit['displayValue']), 0, 250) : null,
            'score' => (float) $score,
            'weight' => $weight,
        ];
    }
}
