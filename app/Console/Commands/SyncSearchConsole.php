<?php

namespace App\Console\Commands;

use App\Services\Seo\SeoKeywordService;
use Illuminate\Console\Command;

/** Daily: Google Search Console figures for the tracked keywords (only when connected). */
class SyncSearchConsole extends Command
{
    protected $signature = 'seo:sync';

    protected $description = 'Fetch the last 28 days of Google Search Console queries for the SEO keywords';

    public function handle(SeoKeywordService $seo): int
    {
        if (! $seo->connected()) {
            $this->info('Search Console is not connected (GSC_CREDENTIALS / GSC_SITE_URL); nothing to do.');

            return self::SUCCESS;
        }

        $this->info('Synced ' . $seo->sync() . ' queries.');

        return self::SUCCESS;
    }
}
