<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EmailFollowUp extends Model
{
    protected $fillable = [
        'campaign_id',
        'name',
        'trigger_type',
        'delay_days',
        'subject',
        'body',
        'template_id',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'delay_days' => 'integer',
        'is_active' => 'boolean',
    ];

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(EmailCampaign::class, 'campaign_id');
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(EmailTemplate::class, 'template_id');
    }

    public function recipients(): HasMany
    {
        return $this->hasMany(EmailCampaignRecipient::class, 'follow_up_id');
    }

    public function getTriggerLabelAttribute(): string
    {
        return match($this->trigger_type) {
            'no_open' => 'لم يفتح',
            'no_click' => 'لم ينقر',
            default => $this->trigger_type,
        };
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
