<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SocialPost extends Model
{
    protected $fillable = [
        'title',
        'content',
        'media_type',
        'media_paths',
        'platforms',
        'status',
        'scheduled_at',
        'published_at',
        'created_by',
    ];

    protected $casts = [
        'media_paths'  => 'array',
        'platforms'    => 'array',
        'scheduled_at' => 'datetime',
        'published_at' => 'datetime',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function results(): HasMany
    {
        return $this->hasMany(SocialPostResult::class, 'post_id');
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'draft'      => 'مسودة',
            'scheduled'  => 'مجدول',
            'publishing' => 'جاري النشر',
            'published'  => 'منشور',
            'failed'     => 'فشل النشر',
            'partial'    => 'نُشر جزئياً',
            default      => $this->status,
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'draft'      => 'secondary',
            'scheduled'  => 'info',
            'publishing' => 'warning',
            'published'  => 'success',
            'failed'     => 'danger',
            'partial'    => 'warning',
            default      => 'secondary',
        };
    }

    public function getMediaTypeIconAttribute(): string
    {
        return match($this->media_type) {
            'text'  => 'fas fa-align-left',
            'image' => 'fas fa-image',
            'video' => 'fas fa-video',
            'reel'  => 'fas fa-film',
            'story' => 'fas fa-circle-notch',
            default => 'fas fa-file',
        };
    }

    public function scopeScheduled($query)
    {
        return $query->where('status', 'scheduled')
                     ->whereNotNull('scheduled_at')
                     ->where('scheduled_at', '<=', now());
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopePublished($query)
    {
        return $query->whereIn('status', ['published', 'partial']);
    }
}
