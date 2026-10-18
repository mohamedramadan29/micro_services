<?php

namespace App\Notifications;

/** A one-off request update with its own text and link (bell, optional email). */
class RequestUpdateNotification extends ServiceRequestNotification
{
    public function __construct(protected string $message, protected string $link, ?string $emailKey = null)
    {
        $this->emailKey = $emailKey;
    }

    protected function text(): string
    {
        return $this->message;
    }

    protected function path(): string
    {
        return $this->link;
    }
}
