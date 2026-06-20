<?php

namespace App\Jobs;

use App\Models\EmailCampaign;
use App\Models\EmailCampaignRecipient;
use App\Services\EmailSendingService;
use App\Services\EmailTrackingService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessCampaignBatchJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public EmailCampaign $campaign;
    public int $tries = 1;
    public int $timeout = 600;

    public function __construct(EmailCampaign $campaign)
    {
        $this->campaign = $campaign;
    }

    public function handle(): void
    {
        $campaign = $this->campaign;

        if (!in_array($campaign->status, ['sending', 'scheduled'])) {
            return;
        }

        $campaign->update(['status' => 'sending']);

        $sendingService = app(EmailSendingService::class);

        if (!$sendingService->canSendToday($campaign)) {
            $this->release(600);
            return;
        }

        $trackingService = app(EmailTrackingService::class);
        $list = $campaign->emailList;

        if (!$list) {
            $campaign->update(['status' => 'failed']);
            return;
        }

        $contacts = $list->contacts()->active()->applyFilters($campaign->filters)->get();

        if ($contacts->isEmpty()) {
            $campaign->update(['status' => 'sent']);
            return;
        }

        $existingEmails = EmailCampaignRecipient::where('campaign_id', $campaign->id)
            ->pluck('email')
            ->toArray();

        $totalContacts = $contacts->count();
        $abThreshold = 0;
        if ($campaign->ab_testing_enabled && $campaign->ab_subject_b) {
            $abThreshold = (int) round(($campaign->ab_split_percent / 100) * $totalContacts);
        }

        $recipientsToCreate = [];
        $createdCount = 0;

        $dripTotalParts = $campaign->drip_enabled ? max(1, $campaign->drip_total_parts) : 1;

        foreach ($contacts as $i => $contact) {
            if (in_array($contact->email, $existingEmails)) {
                continue;
            }

            $abGroup = null;
            if ($campaign->ab_testing_enabled && $campaign->ab_subject_b) {
                $abGroup = $i < $abThreshold ? 'A' : 'B';
            }

            $dripPart = $dripTotalParts > 1 ? ($i % $dripTotalParts) + 1 : 0;

            $recipientsToCreate[] = [
                'campaign_id' => $campaign->id,
                'contact_id' => $contact->id,
                'email' => $contact->email,
                'name' => $contact->name,
                'custom_fields' => $contact->custom_fields ? json_encode($contact->custom_fields) : null,
                'status' => 'pending',
                'tracking_token' => $campaign->tracking_enabled ? $trackingService->generateToken() : null,
                'ab_group' => $abGroup,
                'drip_part' => $dripPart,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            $createdCount++;
        }

        if ($createdCount === 0) {
            $campaign->update(['status' => 'sent']);
            return;
        }

        foreach (array_chunk($recipientsToCreate, 100) as $chunk) {
            EmailCampaignRecipient::insert($chunk);
        }

        $campaign->increment('total_recipients', $createdCount);

        // Send current part recipients only
        $currentPart = $campaign->drip_enabled ? $campaign->drip_current_part + 1 : 0;
        $recipients = EmailCampaignRecipient::where('campaign_id', $campaign->id)
            ->where('status', 'pending')
            ->where('drip_part', $currentPart)
            ->orderBy('id')
            ->get();

        if ($recipients->isEmpty() && !$campaign->drip_enabled) {
            $campaign->update(['status' => 'sent']);
            return;
        }

        $sendInterval = $campaign->send_interval_seconds;

        foreach ($recipients as $delayIndex => $recipient) {
            $delaySeconds = $delayIndex * $sendInterval;
            SendCampaignEmailJob::dispatch($recipient)->delay(now()->addSeconds($delaySeconds));
        }

        if ($campaign->drip_enabled && $dripTotalParts > 1) {
            $campaign->increment('drip_current_part');
            if ($campaign->drip_current_part < $dripTotalParts) {
                ProcessCampaignDripJob::dispatch($campaign)
                    ->delay(now()->addHours($campaign->drip_interval_hours));
            } else {
                $campaign->update(['status' => 'sent']);
            }
        } else {
            $campaign->update(['status' => 'sent']);
        }
    }

    public function failed(\Throwable $e): void
    {
        Log::error('Campaign batch failed: ' . $e->getMessage(), [
            'campaign_id' => $this->campaign->id,
        ]);
        $this->campaign->update(['status' => 'failed']);
    }
}
