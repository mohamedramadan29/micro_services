<?php

namespace App\Jobs;

use App\Models\SiteTest;
use App\Services\SiteTests\SiteTestService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;

/** Runs a free site check in the background (PageSpeed can take up to a minute per device). */
class RunSiteTest implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public int $tries = 1;

    public int $timeout = 240;

    public function __construct(public int $siteTestId)
    {
    }

    public function handle(SiteTestService $service): void
    {
        if ($test = SiteTest::find($this->siteTestId)) {
            $service->run($test);
        }
    }

    public function failed(\Throwable $e): void
    {
        SiteTest::whereKey($this->siteTestId)->whereIn('status', [SiteTest::STATUS_QUEUED, SiteTest::STATUS_RUNNING])
            ->update(['status' => SiteTest::STATUS_FAILED, 'failure_reason' => 'انتهت مهلة الفحص', 'checked_at' => now()]);
    }
}
