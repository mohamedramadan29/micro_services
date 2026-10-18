<?php

namespace App\Notifications;

use App\Models\RequestOffer;

/** To the freelancer: the client chose their offer and paid (money held by the site). */
class OfferSelectedNotification extends ServiceRequestNotification
{
    protected ?string $emailKey = 'offer_selected';

    public function __construct(protected RequestOffer $offer)
    {
    }

    protected function text(): string
    {
        return \App\Support\Msg::t('requests.offer_selected', ['number' => $this->offer->request->number]);
    }

    protected function path(): string
    {
        return '/client-requests/' . $this->offer->request->number;
    }
}
