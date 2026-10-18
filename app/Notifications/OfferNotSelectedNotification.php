<?php

namespace App\Notifications;

use App\Models\RequestOffer;

/** To the other freelancers: the client chose another offer. */
class OfferNotSelectedNotification extends ServiceRequestNotification
{
    protected ?string $emailKey = 'offer_not_selected';

    public function __construct(protected RequestOffer $offer)
    {
    }

    protected function text(): string
    {
        return \App\Support\Msg::t('requests.offer_not_selected', ['number' => $this->offer->request->number]);
    }

    protected function path(): string
    {
        return '/my/offers';
    }
}
