<?php

namespace App\Console;

use App\Jobs\PublishSocialPostJob;
use App\Jobs\ProcessCampaignBatchJob;
use App\Jobs\SendFollowUpEmailJob;
use App\Models\SocialPost;
use App\Models\EmailCampaign;
use App\Models\EmailCampaignRecipient;
use App\Models\EmailFollowUp;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // كل دقيقة: ابحث عن البوستات المجدولة وانشرها
        $schedule->call(function () {
            $posts = SocialPost::where('status', 'scheduled')
                ->whereNotNull('scheduled_at')
                ->where('scheduled_at', '<=', now())
                ->get();

            foreach ($posts as $post) {
                PublishSocialPostJob::dispatch($post);
            }
        })->everyMinute()->name('publish-scheduled-posts')->withoutOverlapping();

        // كل دقيقة: ابحث عن الحملات البريدية المجدولة وابدأ الإرسال
        $schedule->call(function () {
            $campaigns = EmailCampaign::where('status', 'scheduled')
                ->whereNotNull('scheduled_at')
                ->where('scheduled_at', '<=', now())
                ->get();

            foreach ($campaigns as $campaign) {
                ProcessCampaignBatchJob::dispatch($campaign);
            }
        })->everyMinute()->name('process-scheduled-campaigns')->withoutOverlapping();

        // كل دقيقة: ابحث عن المتابعات المستحقة وأرسلها (Sequence Builder)
        $schedule->call(function () {
            $campaigns = EmailCampaign::where('status', 'sent')
                ->whereHas('followUps', function ($q) {
                    $q->active();
                })
                ->get();

            foreach ($campaigns as $campaign) {
                $followUps = $campaign->followUps()
                    ->active()
                    ->orderBy('sort_order')
                    ->orderBy('id')
                    ->get();

                foreach ($followUps as $followUp) {
                    $deadline = now()->subDays($followUp->delay_days);

                    // For the first follow-up: find original campaign recipients
                    // For subsequent follow-ups: find recipients of the previous follow-up
                    if ($followUp->sort_order == 0 && $followUp === $followUps->first()) {
                        $recipients = EmailCampaignRecipient::where('campaign_id', $campaign->id)
                            ->where('status', 'sent')
                            ->whereNull('follow_up_id')
                            ->where('sent_at', '<=', $deadline)
                            ->get();
                    } else {
                        $prevFollowUp = $followUps->where('sort_order', $followUp->sort_order - 1)->first()
                            ?? $followUps->first();

                        $prevSentEmails = EmailCampaignRecipient::where('campaign_id', $campaign->id)
                            ->where('follow_up_id', $prevFollowUp->id)
                            ->where('status', 'sent')
                            ->pluck('email')
                            ->toArray();

                        if (empty($prevSentEmails)) {
                            continue;
                        }

                        $recipients = EmailCampaignRecipient::where('campaign_id', $campaign->id)
                            ->where('status', 'sent')
                            ->whereNull('follow_up_id')
                            ->whereIn('email', $prevSentEmails)
                            ->get();
                    }

                    foreach ($recipients as $recipient) {
                        if ($followUp->trigger_type === 'no_open' && $recipient->opened_count > 0) {
                            continue;
                        }
                        if ($followUp->trigger_type === 'no_click' && $recipient->click_count > 0) {
                            continue;
                        }

                        $alreadySent = EmailCampaignRecipient::where('campaign_id', $campaign->id)
                            ->where('follow_up_id', $followUp->id)
                            ->where('email', $recipient->email)
                            ->exists();

                        if ($alreadySent) {
                            continue;
                        }

                        SendFollowUpEmailJob::dispatch($recipient, $followUp);
                    }
                }
            }
        })->everyMinute()->name('process-email-follow-ups')->withoutOverlapping();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
