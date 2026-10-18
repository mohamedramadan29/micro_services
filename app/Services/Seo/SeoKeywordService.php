<?php

namespace App\Services\Seo;

use App\Models\SeoKeyword;
use App\Models\SeoSearchQuery;
use Illuminate\Support\Facades\DB;

/**
 * Keyword bookkeeping and the Search Console sync (client file «تعديلات»,
 * item 05). Figures come only from Google; without a connection nothing is
 * shown as data.
 */
class SeoKeywordService
{
    public const LIMIT = 25000;

    public function __construct(private SearchConsoleClient $client)
    {
    }

    public function connected(): bool
    {
        return $this->client->configured();
    }

    public function lastSync(): ?\Illuminate\Support\Carbon
    {
        $at = SeoKeyword::max('gsc_synced_at') ?? SeoSearchQuery::max('synced_at');

        return $at ? \Illuminate\Support\Carbon::parse($at) : null;
    }

    /** Last 28 days, ending 3 days ago (Search Console data lags). Returns the number of queries. */
    public function sync(): int
    {
        $end = now()->subDays(3)->toDateString();
        $start = now()->subDays(30)->toDateString();
        $rows = collect($this->client->queries($start, $end, self::LIMIT))
            ->filter(fn ($r) => $r['query'] !== '')
            ->keyBy(fn ($r) => SeoKeyword::normalize($r['query']));
        $complete = $rows->count() < self::LIMIT; // otherwise a missing keyword may just be past the limit
        $now = now();

        DB::transaction(function () use ($rows, $complete, $now) {
            SeoSearchQuery::query()->delete();
            foreach ($rows->values()->chunk(500) as $chunk) {
                SeoSearchQuery::insert($chunk->map(fn ($r) => [
                    'query' => mb_substr($r['query'], 0, 250),
                    'clicks' => $r['clicks'],
                    'impressions' => $r['impressions'],
                    'ctr' => round($r['ctr'], 4),
                    'position' => round($r['position'], 2),
                    'synced_at' => $now,
                ])->all());
            }

            SeoKeyword::query()->lazyById(500)->each(function (SeoKeyword $keyword) use ($rows, $complete, $now) {
                $row = $rows->get($keyword->keyword);
                if (! $row && ! $complete) {
                    return;
                }
                $keyword->update([
                    'gsc_clicks' => $row['clicks'] ?? 0,
                    'gsc_impressions' => $row['impressions'] ?? 0,
                    'gsc_ctr' => $row ? round($row['ctr'], 4) : 0,
                    'gsc_position' => $row ? round($row['position'], 2) : null,
                    'gsc_synced_at' => $now,
                ]);
            });
        });

        return $rows->count();
    }
}
