<?php

namespace App\Notifications;

use App\Models\ServiceRequest;

/** To the client: the request was closed without being carried out. */
class RequestClosedNotification extends ServiceRequestNotification
{
    protected ?string $emailKey = 'request_updates';

    public function __construct(protected ServiceRequest $request, protected bool $refunded = false)
    {
    }

    protected function text(): string
    {
        return \App\Support\Msg::t($this->refunded ? 'requests.closed_refunded' : 'requests.closed', [
            'number' => $this->request->number, 'reason' => $this->request->closeReasonLabel(),
        ]);
    }

    protected function path(): string
    {
        return '/my/requests/' . $this->request->number;
    }
}
