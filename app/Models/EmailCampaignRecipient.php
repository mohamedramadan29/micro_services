<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EmailCampaignRecipient extends Model
{
    protected $fillable = [
        'campaign_id',
        'follow_up_id',
        'ab_group',
        'drip_part',
        'contact_id',
        'email',
        'name',
        'custom_fields',
        'status',
        'tracking_token',
        'sent_at',
        'opened_at',
        'opened_count',
        'clicked_at',
        'click_count',
        'error_message',
    ];

    protected $casts = [
        'custom_fields' => 'array',
        'sent_at' => 'datetime',
        'opened_at' => 'datetime',
        'clicked_at' => 'datetime',
        'opened_count' => 'integer',
        'click_count' => 'integer',
    ];

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(EmailCampaign::class, 'campaign_id');
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(EmailContact::class, 'contact_id');
    }

    public function trackingEvents(): HasMany
    {
        return $this->hasMany(EmailTrackingEvent::class, 'recipient_id');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeSent($query)
    {
        return $query->where('status', 'sent');
    }
}
