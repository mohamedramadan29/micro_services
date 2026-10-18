<?php

namespace App\Notifications;

use App\Models\ServiceRequest;

/** To admins: a client saved a private price inquiry. */
class NewInquiryNotification extends ServiceRequestNotification
{
    protected ?string $emailKey = 'admin_alerts';

    public function __construct(protected ServiceRequest $request)
    {
    }

    protected function text(): string
    {
        return \App\Support\Msg::t('admin.request_inquiry_new', [
            'number' => $this->request->number, 'service' => $this->request->service_name, 'name' => $this->request->contact_name,
        ]);
    }

    protected function path(): string
    {
        return '/admin/requests/' . $this->request->number;
    }
}
