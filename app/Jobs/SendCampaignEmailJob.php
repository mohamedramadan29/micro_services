<?php

namespace App\Jobs;

use App\Mail\CampaignMail;
use App\Models\EmailCampaignRecipient;
use App\Services\EmailMergeService;
use App\Services\EmailTrackingService;
use App\Services\EmailSendingService;
use App\Services\GmailService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SendCampaignEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public EmailCampaignRecipient $recipient;
    public int $tries = 3;
    public int $timeout = 60;

    public function __construct(EmailCampaignRecipient $recipient)
    {
        $this->recipient = $recipient;
    }

    public function handle(): void
    {
        $recipient = $this->recipient;
        $campaign = $recipient->campaign;

        if ($recipient->status !== 'pending') {
            return;
        }

        $mergeService = app(EmailMergeService::class);
        $trackingService = app(EmailTrackingService::class);
        $sendingService = app(EmailSendingService::class);

        if (!$sendingService->canSendToday($campaign)) {
            $this->release(600);
            return;
        }

        $customFields = array_merge(
            $recipient->custom_fields ?? [],
            [
                'name' => $recipient->name,
                'email' => $recipient->email,
            ]
        );

        if ($campaign->tracking_enabled && $recipient->tracking_token) {
            $customFields['tracking_pixel'] = $trackingService->getOpenTrackingUrl($recipient->tracking_token);
            $customFields['unsubscribe_url'] = $trackingService->getUnsubscribeUrl($recipient->tracking_token);
        }

        $campaignSubject = ($campaign->ab_testing_enabled && $recipient->ab_group === 'B' && $campaign->ab_subject_b)
            ? $campaign->ab_subject_b
            : $campaign->subject;

        $subject = $mergeService->mergeSubject($campaignSubject, $customFields);
        $body = $mergeService->mergeBody($campaign->body, $customFields);

        if ($campaign->tracking_enabled && $recipient->tracking_token) {
            $trackingUrl = $trackingService->getOpenTrackingUrl($recipient->tracking_token);
            $body = $mergeService->replaceTrackingPixel($body, $trackingUrl);

            $clickBaseUrl = $trackingService->getClickTrackingUrl($recipient->tracking_token);
            $body = $mergeService->replaceLinks($body, $clickBaseUrl);

            $unsubscribeUrl = $trackingService->getUnsubscribeUrl($recipient->tracking_token);
            $body = $mergeService->appendUnsubscribeLink($body, $unsubscribeUrl);
        }

        try {
            if ($campaign->mailer === 'gmail') {
                $gmail = app(GmailService::class);
                $gmail->send(
                    $recipient->email,
                    $subject,
                    $body,
                    [
                        'reply_to' => $campaign->reply_to,
                        'cc' => $campaign->cc_email,
                        'bcc' => $campaign->bcc_email,
                        'from_name' => $campaign->sender_name,
                        'from_email' => $campaign->sender_email,
                    ]
                );
            } else {
                Mail::mailer('smtp')->send(new CampaignMail($recipient, $subject, $body));
            }

            $recipient->update([
                'status' => 'sent',
                'sent_at' => now(),
            ]);

            $campaign->increment('sent_count');
            $sendingService->logSend($campaign, $recipient->id, $campaign->mailer);

        } catch (\Exception $e) {
            Log::error('Campaign email failed: ' . $e->getMessage(), [
                'recipient_id' => $recipient->id,
                'campaign_id' => $campaign->id,
                'email' => $recipient->email,
            ]);

            $recipient->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);

            if ($this->attempts() >= $this->tries) {
                $trackingService->trackBounce($recipient, $e->getMessage());
            } else {
                $this->release(60);
            }
        }
    }
}
