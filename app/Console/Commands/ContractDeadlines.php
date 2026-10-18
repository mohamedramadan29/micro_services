<?php

namespace App\Console\Commands;

use App\Services\Contracts\StageClock;
use Illuminate\Console\Command;

class ContractDeadlines extends Command
{
    protected $signature = 'contracts:deadlines';

    protected $description = 'Service 3: stage due reminders, lateness alerts, review and pause reminders/escalations (each sent once)';

    public function handle(StageClock $clock, \App\Services\Contracts\DisputeService $disputes, \App\Services\Contracts\ContractAdminTools $tools): int
    {
        foreach ($clock->tick() + ['disputes_to_admin' => $disputes->tick(), 'reply_requests_overdue' => $tools->tick()] as $what => $count) {
            if ($count) {
                $this->line("$what: $count");
            }
        }

        return self::SUCCESS;
    }
}
