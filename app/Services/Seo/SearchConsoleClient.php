<?php

namespace App\Services\Seo;

/** Google Search Console search analytics (the real client, or a fake in tests). */
interface SearchConsoleClient
{
    /** Credentials and the site property are set. */
    public function configured(): bool;

    /**
     * Top queries of the site between two dates (Y-m-d).
     *
     * @return array<int, array{query: string, clicks: int, impressions: int, ctr: float, position: float}>
     */
    public function queries(string $start, string $end, int $limit): array;
}
