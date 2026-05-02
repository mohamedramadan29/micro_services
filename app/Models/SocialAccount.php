<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SocialAccount extends Model
{
    protected $fillable = [
        'platform',
        'account_name',
        'account_id',
        'access_token',
        'refresh_token',
        'token_expires_at',
        'page_id',
        'page_name',
        'avatar',
        'is_active',
        'meta',
    ];

    protected $casts = [
        'is_active'        => 'boolean',
        'token_expires_at' => 'datetime',
        'meta'             => 'array',
    ];

    // تشفير التوكنات تلقائياً عند الحفظ
    public function setAccessTokenAttribute($value): void
    {
        $this->attributes['access_token'] = encrypt($value);
    }

    public function getAccessTokenAttribute($value): string
    {
        return decrypt($value);
    }

    public function setRefreshTokenAttribute($value): void
    {
        if ($value) {
            $this->attributes['refresh_token'] = encrypt($value);
        }
    }

    public function getRefreshTokenAttribute($value): ?string
    {
        return $value ? decrypt($value) : null;
    }

    public function isTokenExpired(): bool
    {
        if (!$this->token_expires_at) {
            return false;
        }
        return $this->token_expires_at->isPast();
    }

    public function getPlatformLabelAttribute(): string
    {
        return match($this->platform) {
            'facebook'  => 'Facebook',
            'instagram' => 'Instagram',
            'tiktok'    => 'TikTok',
            'youtube'   => 'YouTube',
            'twitter'   => 'Twitter / X',
            'linkedin'  => 'LinkedIn',
            default     => ucfirst($this->platform),
        };
    }

    public function getPlatformColorAttribute(): string
    {
        return match($this->platform) {
            'facebook'  => '#1877F2',
            'instagram' => '#E4405F',
            'tiktok'    => '#000000',
            'youtube'   => '#FF0000',
            'twitter'   => '#1DA1F2',
            'linkedin'  => '#0A66C2',
            default     => '#6c757d',
        };
    }

    public function getPlatformIconAttribute(): string
    {
        return match($this->platform) {
            'facebook'  => 'fab fa-facebook-f',
            'instagram' => 'fab fa-instagram',
            'tiktok'    => 'fab fa-tiktok',
            'youtube'   => 'fab fa-youtube',
            'twitter'   => 'fab fa-twitter',
            'linkedin'  => 'fab fa-linkedin-in',
            default     => 'fas fa-share-alt',
        };
    }

    public function postResults(): HasMany
    {
        return $this->hasMany(SocialPostResult::class, 'account_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopePlatform($query, string $platform)
    {
        return $query->where('platform', $platform);
    }
}
