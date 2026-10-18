<?php

namespace App\Notifications;

use App\Models\OfferMessage;

/** To the other party of an offer: a new message in the offer's conversation. */
class OfferMessageNotification extends ServiceRequestNotification
{
    protected ?string $emailKey = 'offer_messages';

    public function __construct(protected OfferMessage $message)
    {
    }

    protected function text(): string
    {
        $request = $this->message->request;
        $fromClient = (int) $this->message->sender_id === (int) $request->user_id;

        return \App\Support\Msg::t($fromClient ? 'requests.offer_message_from_client' : 'requests.offer_message_from_freelancer', [
            'number' => $request->number,
        ]);
    }

    protected function path(): string
    {
        return '/offers/' . $this->message->request_offer_id . '/chat';
    }
}
