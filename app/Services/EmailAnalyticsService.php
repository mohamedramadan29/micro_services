<?php

namespace App\Services;

use App\Models\EmailCampaign;
use App\Models\EmailCampaignRecipient;

class EmailAnalyticsService
{
    public function getCampaignStats(EmailCampaign $campaign): array
    {
        $total = $campaign->total_recipients;
        $sent = $campaign->sent_count;
        $opens = $campaign->open_count;
        $clicks = $campaign->click_count;

        return [
            'total' => $total,
            'sent' => $sent,
            'opens' => $opens,
            'clicks' => $clicks,
            'open_rate' => $sent > 0 ? round(($opens / $sent) * 100, 1) : 0,
            'click_rate' => $sent > 0 ? round(($clicks / $sent) * 100, 1) : 0,
            'unique_opens' => EmailCampaignRecipient::where('campaign_id', $campaign->id)
                ->where('opened_count', '>', 0)->count(),
            'unique_clicks' => EmailCampaignRecipient::where('campaign_id', $campaign->id)
                ->where('click_count', '>', 0)->count(),
        ];
    }

    public function getOverallStats(): array
    {
        $totalSent = EmailCampaign::sum('sent_count');
        $totalOpens = EmailCampaign::sum('open_count');
        $totalClicks = EmailCampaign::sum('click_count');

        return [
            'total_campaigns' => EmailCampaign::count(),
            'total_sent' => $totalSent,
            'total_opens' => $totalOpens,
            'total_clicks' => $totalClicks,
            'overall_open_rate' => $totalSent > 0 ? round(($totalOpens / $totalSent) * 100, 1) : 0,
            'overall_click_rate' => $totalSent > 0 ? round(($totalClicks / $totalSent) * 100, 1) : 0,
        ];
    }
}
