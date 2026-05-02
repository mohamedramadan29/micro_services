<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SocialPostResult extends Model
{
    protected $fillable = [
        'post_id',
        'account_id',
        'platform',
        'platform_post_id',
        'status',
        'error_message',
        'published_at',
        'engagement',
    ];

    protected $casts = [
        'engagement'   => 'array',
        'published_at' => 'datetime',
    ];

    public function post(): BelongsTo
    {
        return $this->belongsTo(SocialPost::class, 'post_id');
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(SocialAccount::class, 'account_id');
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'pending'   => 'في الانتظار',
            'published' => 'منشور',
            'failed'    => 'فشل',
            default     => $this->status,
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'pending'   => 'warning',
            'published' => 'success',
            'failed'    => 'danger',
            default     => 'secondary',
        };
    }
}
