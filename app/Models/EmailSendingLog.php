<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmailSendingLog extends Model
{
    protected $fillable = [
        'campaign_id',
        'recipient_id',
        'mailer',
        'sent_at',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
    ];

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(EmailCampaign::class);
    }

    public function recipient(): BelongsTo
    {
        return $this->belongsTo(EmailCampaignRecipient::class);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('sent_at', today());
    }

    public function scopeThisHour($query)
    {
        return $query->where('sent_at', '>=', now()->subHour());
    }
}
