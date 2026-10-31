<?php

namespace App\Notifications;

use App\Models\Suggestion;

/** To admins: a new suggestion, or one assigned to them. */
class SuggestionAdminNotification extends ServiceRequestNotification
{
    protected ?string $emailKey = 'suggestions_admin';

    public function __construct(protected Suggestion $suggestion, protected bool $assigned = false)
    {
    }

    protected function text(): string
    {
        return \App\Support\Msg::t($this->assigned ? 'admin.suggestion_assigned' : 'admin.suggestion_new', [
            'number' => $this->suggestion->reference_code, 'title' => $this->suggestion->title,
            'category' => $this->suggestion->categoryLabel(), 'name' => $this->suggestion->senderName(),
        ]);
    }

    protected function path(): string
    {
        return '/admin/suggestions/' . $this->suggestion->reference_code;
    }
}
