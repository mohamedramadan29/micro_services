<?php

namespace App\Notifications;

use App\Models\HelpRequest;

/** To admins: a new «ساعدوني أختار» request, or one assigned to them. */
class HelpRequestNotification extends ServiceRequestNotification
{
    protected ?string $emailKey = 'help_requests';

    public function __construct(protected HelpRequest $help, protected bool $assigned = false)
    {
    }

    protected function text(): string
    {
        return \App\Support\Msg::t($this->assigned ? 'admin.help_request_assigned' : 'admin.help_request_new', [
            'number' => $this->help->number, 'service' => $this->help->serviceLabel(), 'name' => $this->help->user?->name,
        ]);
    }

    protected function path(): string
    {
        return '/admin/help-requests/' . $this->help->number;
    }
}
