<?php

namespace App\Notifications;

use App\Models\RequestOffer;

/** To the client: a freelancer sent (or updated) an offer on their request. */
class NewOfferNotification extends ServiceRequestNotification
{
    protected ?string $emailKey = 'new_offer';

    public function __construct(protected RequestOffer $offer, protected bool $updated = false)
    {
    }

    protected function text(): string
    {
        $request = $this->offer->request;

        return \App\Support\Msg::t($this->updated ? 'requests.offer_updated' : 'requests.offer_new', [
            'number' => $request->number, 'service' => $request->service_name,
        ]);
    }

    protected function path(): string
    {
        return '/my/requests/' . $this->offer->request->number . '#offers';
    }
}
