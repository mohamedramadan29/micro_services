<?php

namespace App\Console\Commands;

use App\Models\AnalyticsVisit;
use Illuminate\Console\Command;

class PruneAnalytics extends Command
{
    protected $signature = 'analytics:prune {--months=13 : Keep this many months of visits}';

    protected $description = 'Delete journey-analytics visits (and their events) older than the retention period';

    public function handle(): int
    {
        $before = now()->subMonths(max(1, (int) $this->option('months')));
        $deleted = 0;
        AnalyticsVisit::where('started_at', '<', $before)->select('id')->chunkById(1000, function ($visits) use (&$deleted) {
            $deleted += AnalyticsVisit::whereKey($visits->pluck('id'))->delete();
        });
        $this->info("Deleted {$deleted} old visits.");
        // The notification delivery log keeps 90 days.
        \App\Models\NotificationLog::where('created_at', '<', now()->subDays(90))->delete();

        return self::SUCCESS;
    }
}
