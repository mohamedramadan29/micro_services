<?php

namespace App\Notifications;

use App\Models\ServiceRequest;

/** To the client: the freelancer delivered the work and waits for confirmation. */
class WorkDeliveredNotification extends ServiceRequestNotification
{
    protected ?string $emailKey = 'work_delivered';

    public function __construct(protected ServiceRequest $request)
    {
    }

    protected function text(): string
    {
        return \App\Support\Msg::t('requests.work_delivered', ['number' => $this->request->number]);
    }

    protected function path(): string
    {
        return '/my/requests/' . $this->request->number;
    }
}
