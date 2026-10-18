<?php

namespace App\Console\Commands;

use App\Models\RequestOffer;
use App\Models\ServiceRequest;
use App\Models\User;
use App\Notifications\NoOffersAlertNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;

/**
 * Page 18 of the spec: when a published request has had no offer for an
 * hour, alert the admins once. Price inquiries are never published, so they
 * never match.
 */
class AlertRequestsWithoutOffers extends Command
{
    public const AFTER_MINUTES = 60;

    protected $signature = 'requests:alert-no-offers';

    protected $description = 'Alert admins about published requests with no offers after one hour';

    public function handle(): int
    {
        $admins = User::activeAdmins()->get();
        $count = 0;

        ServiceRequest::where('status', ServiceRequest::STATUS_OPEN)
            ->whereNull('no_offer_alerted_at')
            ->where('published_at', '<=', now()->subMinutes(self::AFTER_MINUTES))
            ->whereDoesntHave('offers', fn ($query) => $query->where('status', '!=', RequestOffer::STATUS_WITHDRAWN))
            ->lazyById(200)
            ->each(function (ServiceRequest $request) use ($admins, &$count) {
                // Claim the request first so a parallel run can't alert twice.
                $claimed = ServiceRequest::whereKey($request->id)->whereNull('no_offer_alerted_at')
                    ->update(['no_offer_alerted_at' => now(), 'followup_status' => ServiceRequest::FOLLOWUP_NEW, 'followup_updated_at' => now()]);
                if ($claimed) {
                    Notification::send($admins, new NoOffersAlertNotification($request->refresh()));
                    $count++;
                }
            });

        $this->info("Alerted admins about {$count} request(s).");

        return self::SUCCESS;
    }
}
