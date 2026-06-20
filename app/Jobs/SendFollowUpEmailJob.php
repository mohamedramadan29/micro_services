<?php

namespace App\Jobs;

use App\Mail\CampaignMail;
use App\Models\EmailFollowUp;
use App\Models\EmailCampaignRecipient;
use App\Services\EmailMergeService;
use App\Services\EmailTrackingService;
use App\Services\EmailSendingService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SendFollowUpEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public EmailCampaignRecipient $recipient;
    public EmailFollowUp $followUp;
    public int $tries = 3;
    public int $timeout = 60;

    public function __construct(EmailCampaignRecipient $recipient, EmailFollowUp $followUp)
    {
        $this->recipient = $recipient;
        $this->followUp = $followUp;
    }

    public function handle(): void
    {
        $recipient = $this->recipient;
        $followUp = $this->followUp;
        $campaign = $followUp->campaign;

        if ($recipient->status !== 'sent') {
            return;
        }

        $alreadySent = EmailCampaignRecipient::where('campaign_id', $campaign->id)
            ->where('follow_up_id', $followUp->id)
            ->where('email', $recipient->email)
            ->exists();

        if ($alreadySent) {
            return;
        }

        $mergeService = app(EmailMergeService::class);
        $trackingService = app(EmailTrackingService::class);
        $sendingService = app(EmailSendingService::class);

        if (!$sendingService->canSendToday($campaign)) {
            $this->release(300);
            return;
        }

        $customFields = array_merge(
            $recipient->custom_fields ?? [],
            [
                'name' => $recipient->name,
                'email' => $recipient->email,
            ]
        );

        $token = $trackingService->generateToken();

        if ($campaign->tracking_enabled) {
            $customFields['tracking_pixel'] = $trackingService->getOpenTrackingUrl($token);
            $customFields['unsubscribe_url'] = $trackingService->getUnsubscribeUrl($token);
        }

        $subject = $mergeService->mergeSubject($followUp->subject, $customFields);
        $body = $mergeService->mergeBody($followUp->body, $customFields);

        if ($campaign->tracking_enabled) {
            $trackingUrl = $trackingService->getOpenTrackingUrl($token);
            $body = $mergeService->replaceTrackingPixel($body, $trackingUrl);

            $clickBaseUrl = $trackingService->getClickTrackingUrl($token);
            $body = $mergeService->replaceLinks($body, $clickBaseUrl);

            $unsubscribeUrl = $trackingService->getUnsubscribeUrl($token);
            $body = $mergeService->appendUnsubscribeLink($body, $unsubscribeUrl);
        }

        try {
            Mail::mailer('smtp')->send(new CampaignMail($recipient, $subject, $body));

            EmailCampaignRecipient::create([
                'campaign_id' => $campaign->id,
                'follow_up_id' => $followUp->id,
                'contact_id' => $recipient->contact_id,
                'email' => $recipient->email,
                'name' => $recipient->name,
                'custom_fields' => $recipient->custom_fields,
                'status' => 'sent',
                'tracking_token' => $campaign->tracking_enabled ? $token : null,
                'sent_at' => now(),
            ]);

            $campaign->increment('sent_count');
            $sendingService->incrementDailyCount($campaign);

        } catch (\Exception $e) {
            Log::error('Follow-up email failed: ' . $e->getMessage(), [
                'follow_up_id' => $followUp->id,
                'campaign_id' => $campaign->id,
                'email' => $recipient->email,
            ]);

            if ($this->attempts() >= $this->tries) {
                $trackingService->trackBounce($recipient, $e->getMessage());
            } else {
                $this->release(60);
            }
        }
    }
}
