<?php

namespace App\Jobs;

use App\Models\EmailCampaign;
use App\Services\EmailSendingService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessCampaignDripJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public EmailCampaign $campaign;
    public int $tries = 3;
    public int $timeout = 300;

    public function __construct(EmailCampaign $campaign)
    {
        $this->campaign = $campaign;
    }

    public function handle(): void
    {
        $campaign = $this->campaign;

        if (!$campaign->drip_enabled) {
            return;
        }

        $sendingService = app(EmailSendingService::class);
        if (!$sendingService->canSendToday($campaign)) {
            $this->release(600);
            return;
        }

        $currentPart = $campaign->drip_current_part;

        if ($currentPart >= $campaign->drip_total_parts) {
            $campaign->update(['status' => 'sent']);
            return;
        }

        $recipients = $campaign->recipients()
            ->where('drip_part', $currentPart + 1)
            ->where('status', 'pending')
            ->orderBy('id')
            ->get();

        if ($recipients->isEmpty()) {
            $campaign->update(['status' => 'sent']);
            return;
        }

        $sendInterval = $campaign->send_interval_seconds;

        foreach ($recipients as $delayIndex => $recipient) {
            $delaySeconds = $delayIndex * $sendInterval;
            SendCampaignEmailJob::dispatch($recipient)->delay(now()->addSeconds($delaySeconds));
        }

        $campaign->increment('drip_current_part');

        if ($campaign->drip_current_part < $campaign->drip_total_parts) {
            self::dispatch($campaign)->delay(now()->addHours($campaign->drip_interval_hours));
        } else {
            $campaign->update(['status' => 'sent']);
        }
    }

    public function failed(\Throwable $e): void
    {
        Log::error('Campaign drip job failed: ' . $e->getMessage(), [
            'campaign_id' => $this->campaign->id,
        ]);
    }
}
