<?php

namespace App\Services\Currency;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * EGP → USD rate for showing approximate dollar prices (user decision
 * 2026-10-15: site-wide, display only — EasyKash always charges EGP).
 * Free open.er-api.com feed, refreshed by `fx:refresh`; a rate older than
 * three days is not shown, so a stale number never looks current.
 */
class ExchangeRates
{
    public const CACHE_KEY = 'fx.egp_usd';

    public const MAX_AGE_HOURS = 72;

    public function usdPerEgp(): ?float
    {
        $cached = Cache::get(self::CACHE_KEY);
        if (! is_array($cached) || empty($cached['rate']) || empty($cached['at'])) {
            return null;
        }

        $age = \Illuminate\Support\Carbon::createFromTimestamp($cached['at'])->diffInHours(now()); // positive for a past fetch

        return $age <= self::MAX_AGE_HOURS ? (float) $cached['rate'] : null;
    }

    public function updatedAt(): ?\Illuminate\Support\Carbon
    {
        $cached = Cache::get(self::CACHE_KEY);

        return is_array($cached) && ! empty($cached['at']) ? \Illuminate\Support\Carbon::createFromTimestamp($cached['at']) : null;
    }

    /** Fetch today's rate; keeps the last good one when the feed fails. */
    public function refresh(): ?float
    {
        try {
            $response = Http::timeout(15)->get((string) config('services.fx.url', 'https://open.er-api.com/v6/latest/EGP'));
            $rate = $response->successful() && $response->json('result') === 'success' ? $response->json('rates.USD') : null;
        } catch (\Throwable $e) {
            $rate = null;
            Log::warning('Exchange rate fetch failed', ['error' => $e->getMessage()]);
        }
        if (! is_numeric($rate) || $rate <= 0 || $rate > 1) { // 1 EGP is far below 1 USD; anything else is a bad answer
            return null;
        }

        Cache::forever(self::CACHE_KEY, ['rate' => (float) $rate, 'at' => now()->getTimestamp()]);

        return (float) $rate;
    }
}
