<?php

namespace App\Services;

use App\Models\EmailCampaign;
use App\Models\EmailSendingLog;

class EmailSendingService
{
    public function getBatchSize(): int
    {
        return 100;
    }

    public function canSendToday(EmailCampaign $campaign): bool
    {
        $todayCount = EmailSendingLog::whereDate('sent_at', today())->count();
        if ($todayCount >= $campaign->daily_limit) {
            return false;
        }

        $hourCount = EmailSendingLog::where('sent_at', '>=', now()->subHour())->count();
        if ($hourCount >= $campaign->throttle_per_hour) {
            return false;
        }

        return true;
    }

    public function incrementDailyCount(EmailCampaign $campaign): void
    {
        // logged via EmailSendingLog creation
    }

    public function logSend(EmailCampaign $campaign, int $recipientId, string $mailer = 'smtp'): void
    {
        EmailSendingLog::create([
            'campaign_id' => $campaign->id,
            'recipient_id' => $recipientId,
            'mailer' => $mailer,
            'sent_at' => now(),
        ]);
    }

    public function getTodayCount(): int
    {
        return EmailSendingLog::whereDate('sent_at', today())->count();
    }

    public function getRemainingToday(EmailCampaign $campaign): int
    {
        $used = EmailSendingLog::whereDate('sent_at', today())->count();
        return max(0, $campaign->daily_limit - $used);
    }

    public function getRemainingThisHour(EmailCampaign $campaign): int
    {
        $used = EmailSendingLog::where('sent_at', '>=', now()->subHour())->count();
        return max(0, $campaign->throttle_per_hour - $used);
    }
}
