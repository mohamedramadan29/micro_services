<?php

namespace App\Services;

use App\Models\EmailCampaignRecipient;
use Illuminate\Support\Str;

class EmailTrackingService
{
    public function generateToken(): string
    {
        return (string) Str::uuid();
    }

    public function getOpenTrackingUrl(string $token): string
    {
        return route('track.open', ['token' => $token]);
    }

    public function getClickTrackingUrl(string $token): string
    {
        return route('track.click', ['token' => $token]);
    }

    public function getUnsubscribeUrl(string $token): string
    {
        return route('track.unsubscribe', ['token' => $token]);
    }

    public function getRecipientByToken(string $token): ?EmailCampaignRecipient
    {
        return EmailCampaignRecipient::where('tracking_token', $token)->first();
    }

    public function trackOpen(EmailCampaignRecipient $recipient, string $userAgent, string $ip): void
    {
        $recipient->campaign->increment('open_count');
        $recipient->increment('opened_count');
        if (!$recipient->opened_at) {
            $recipient->opened_at = now();
        }
        $recipient->save();

        $recipient->trackingEvents()->create([
            'campaign_id' => $recipient->campaign_id,
            'contact_id' => $recipient->contact_id,
            'event_type' => 'open',
            'user_agent' => $userAgent,
            'ip_address' => $ip,
        ]);
    }

    public function trackClick(EmailCampaignRecipient $recipient, string $url, string $userAgent, string $ip): void
    {
        $recipient->campaign->increment('click_count');
        $recipient->increment('click_count');
        if (!$recipient->clicked_at) {
            $recipient->clicked_at = now();
        }
        $recipient->save();

        $recipient->trackingEvents()->create([
            'campaign_id' => $recipient->campaign_id,
            'contact_id' => $recipient->contact_id,
            'event_type' => 'click',
            'url' => $url,
            'user_agent' => $userAgent,
            'ip_address' => $ip,
        ]);
    }

    public function trackUnsubscribe(EmailCampaignRecipient $recipient): void
    {
        $recipient->update(['status' => 'unsubscribed']);
        $recipient->contact?->update(['unsubscribed_at' => now()]);

        $recipient->trackingEvents()->create([
            'campaign_id' => $recipient->campaign_id,
            'contact_id' => $recipient->contact_id,
            'event_type' => 'unsubscribe',
        ]);
    }

    public function trackBounce(EmailCampaignRecipient $recipient, string $errorMessage = null): void
    {
        $recipient->update([
            'status' => 'bounced',
            'error_message' => $errorMessage,
        ]);

        $recipient->trackingEvents()->create([
            'campaign_id' => $recipient->campaign_id,
            'contact_id' => $recipient->contact_id,
            'event_type' => 'bounce',
        ]);
    }
}
