<?php

namespace App\Console\Commands;

use App\Models\SiteTest;
use App\Models\SiteTestResult;
use App\Services\SiteTests\SiteTestSettings;
use Illuminate\Console\Command;

/**
 * Retention of the free site checks (admin setting «مدة حفظ النتائج»):
 * anonymous checks (no account, no contact data, no request) are deleted;
 * the others keep scores and issues but lose the raw engine JSON.
 */
class PruneSiteTests extends Command
{
    protected $signature = 'site-tests:prune';

    protected $description = 'Delete or slim free site checks older than the retention period';

    public function handle(): int
    {
        $days = (int) SiteTestSettings::get('retention_days');
        if ($days <= 0) {
            $this->info('Retention is off (keep forever).');

            return self::SUCCESS;
        }
        $before = now()->subDays($days);

        $deleted = 0;
        SiteTest::where('created_at', '<', $before)
            ->whereNull('user_id')->whereNull('contact_phone')->whereNull('contact_email')
            ->whereNull('service_request_id')->whereNull('choice')
            ->whereNull('review_summary')->whereNull('assigned_to')->whereNull('team_notes')
            ->whereDoesntHave('issues', fn ($q) => $q->where('source', 'team'))
            ->select('id')
            ->chunkById(500, function ($tests) use (&$deleted) {
                $deleted += SiteTest::whereKey($tests->pluck('id'))->delete();
            });

        $slimmed = SiteTestResult::whereNotNull('raw')
            ->whereIn('site_test_id', SiteTest::where('created_at', '<', $before)->select('id'))
            ->update(['raw' => null]);

        $this->info("Deleted {$deleted} anonymous checks, cleared raw data of {$slimmed} results.");

        return self::SUCCESS;
    }
}
