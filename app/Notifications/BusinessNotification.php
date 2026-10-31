<?php

namespace App\Notifications;

/**
 * «نفّذها للأعمال»: one notification class for the company requests/projects; the text is an admin-editable
 * template (Templates/business.php). Email key `business` (the client) or `business_admin` (the team).
 */
class BusinessNotification extends ServiceRequestNotification
{
    public function __construct(protected string $key, protected array $vars, protected string $url, string $emailKey)
    {
        $this->emailKey = $emailKey;
    }

    protected function text(): string
    {
        return \App\Support\Msg::t($this->key, $this->vars);
    }

    protected function path(): string
    {
        return $this->url;
    }
}
