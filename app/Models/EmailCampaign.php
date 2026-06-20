<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EmailCampaign extends Model
{
    protected $fillable = [
        'name',
        'email_list_id',
        'template_id',
        'subject',
        'body',
        'sender_name',
        'sender_email',
        'reply_to',
        'cc_email',
        'bcc_email',
        'has_attachment',
        'attachment_path',
        'attachment_name',
        'scheduled_at',
        'status',
        'total_recipients',
        'sent_count',
        'open_count',
        'click_count',
        'tracking_enabled',
        'mailer',
        'filters',
        'ab_testing_enabled',
        'ab_subject_b',
        'ab_split_percent',
        'send_interval_seconds',
        'throttle_per_hour',
        'daily_limit',
        'drip_enabled',
        'drip_total_parts',
        'drip_current_part',
        'drip_interval_hours',
        'created_by',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'tracking_enabled' => 'boolean',
        'has_attachment' => 'boolean',
        'filters' => 'array',
        'ab_testing_enabled' => 'boolean',
        'ab_split_percent' => 'integer',
        'total_recipients' => 'integer',
        'sent_count' => 'integer',
        'open_count' => 'integer',
        'click_count' => 'integer',
        'send_interval_seconds' => 'integer',
        'throttle_per_hour' => 'integer',
        'daily_limit' => 'integer',
        'drip_enabled' => 'boolean',
        'drip_total_parts' => 'integer',
        'drip_current_part' => 'integer',
        'drip_interval_hours' => 'integer',
    ];

    public function emailList(): BelongsTo
    {
        return $this->belongsTo(EmailList::class, 'email_list_id');
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(EmailTemplate::class, 'template_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function recipients(): HasMany
    {
        return $this->hasMany(EmailCampaignRecipient::class, 'campaign_id');
    }

    public function trackingEvents(): HasMany
    {
        return $this->hasMany(EmailTrackingEvent::class, 'campaign_id');
    }

    public function followUps(): HasMany
    {
        return $this->hasMany(EmailFollowUp::class, 'campaign_id');
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'draft'     => 'مسودة',
            'scheduled' => 'مجدولة',
            'sending'   => 'جارٍ الإرسال',
            'sent'      => 'تم الإرسال',
            'paused'    => 'متوقفة',
            'failed'    => 'فشلت',
            default     => $this->status,
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'draft'     => 'secondary',
            'scheduled' => 'info',
            'sending'   => 'warning',
            'sent'      => 'success',
            'paused'    => 'danger',
            'failed'    => 'danger',
            default     => 'secondary',
        };
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopeScheduled($query)
    {
        return $query->where('status', 'scheduled');
    }
}
