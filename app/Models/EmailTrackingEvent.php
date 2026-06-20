<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmailTrackingEvent extends Model
{
    protected $fillable = [
        'campaign_id',
        'recipient_id',
        'contact_id',
        'event_type',
        'url',
        'user_agent',
        'ip_address',
    ];

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(EmailCampaign::class, 'campaign_id');
    }

    public function recipient(): BelongsTo
    {
        return $this->belongsTo(EmailCampaignRecipient::class, 'recipient_id');
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(EmailContact::class, 'contact_id');
    }
}
