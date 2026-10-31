<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * «شاركنا اقتراحك» (client file «نظام الاقتراحات», 2026-10-18): an idea from a client, a freelancer
 * or a visitor. Private (no public list, no voting); kept apart from help requests, tickets and service requests.
 */
class Suggestion extends Model
{
    public const STATUS_NEW = 'new';
    public const STATUS_REVIEWING = 'reviewing';
    public const STATUS_PLANNED = 'planned';
    public const STATUS_DONE = 'done';
    public const STATUS_CLOSED = 'closed';

    public const STATUSES = [
        self::STATUS_NEW => 'جديد',
        self::STATUS_REVIEWING => 'قيد المراجعة',
        self::STATUS_PLANNED => 'مخطط للتنفيذ',
        self::STATUS_DONE => 'تم التنفيذ',
        self::STATUS_CLOSED => 'مغلق',
    ];

    /** Status changes the sender is told about (besides the receipt); internal notes never notify. */
    public const NOTIFIED_STATUSES = [self::STATUS_PLANNED, self::STATUS_DONE, self::STATUS_CLOSED];

    public const ROLES = [
        'client' => 'عميل',
        'freelancer' => 'مستقل',
        'guest' => 'زائر',
        'employee' => 'موظف',
        'admin' => 'إدارة',
    ];

    public const PRIORITIES = ['low' => 'منخفضة', 'normal' => 'عادية', 'high' => 'عالية'];

    public const TITLE_MAX = 120;
    public const DESCRIPTION_MIN = 20;
    public const DESCRIPTION_MAX = 2000;

    protected $guarded = [];

    protected $casts = ['wants_updates' => 'boolean'];

    public function getRouteKeyName(): string
    {
        return 'reference_code';
    }

    public static function roleOf(?User $user): string
    {
        return match ($user?->account_type) {
            null => 'guest',
            User::TYPE_FREELANCER => 'freelancer',
            User::TYPE_EMPLOYEE => 'employee',
            User::TYPE_ADMIN => 'admin',
            default => 'client',
        };
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function category()
    {
        return $this->belongsTo(SuggestionCategory::class, 'category_id');
    }

    public function assignee()
    {
        return $this->belongsTo(User::class, 'assigned_admin_id');
    }

    public function duplicateOf()
    {
        return $this->belongsTo(self::class, 'duplicate_of_id');
    }

    public function duplicates()
    {
        return $this->hasMany(self::class, 'duplicate_of_id');
    }

    public function history()
    {
        return $this->hasMany(SuggestionStatusHistory::class)->orderByDesc('id');
    }

    public function notes()
    {
        return $this->hasMany(SuggestionAdminNote::class)->orderByDesc('id');
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    /** Reuses the site's request status pills (rf-status-*). */
    public function statusCss(): string
    {
        return 'rf-status-' . match ($this->status) {
            self::STATUS_REVIEWING => 'in_progress',
            self::STATUS_PLANNED => 'open',
            self::STATUS_DONE => 'completed',
            self::STATUS_CLOSED => 'not_completed',
            default => 'inquiry',
        };
    }

    public function roleLabel(): string
    {
        return self::ROLES[$this->user_role] ?? $this->user_role;
    }

    public function categoryLabel(): string
    {
        return $this->category?->name ?? $this->category_name ?? '—';
    }

    public function senderName(): string
    {
        return $this->user?->name ?? ($this->guest_name ?: 'زائر');
    }

    /** Where updates go: the account's email, or the visitor's when he asked for updates. */
    public function senderEmail(): ?string
    {
        return $this->user ? $this->user->email : ($this->wants_updates ? $this->guest_email : null);
    }
}
