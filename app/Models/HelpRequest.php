<?php

namespace App\Models;

use App\Models\admin\SubCategory;
use Illuminate\Database\Eloquent\Model;

/** «ساعدوني أختار»: a client asks the team which service fits (kept apart from service requests). */
class HelpRequest extends Model
{
    public const STATUS_NEW = 'new';
    public const STATUS_FOLLOWING = 'following';
    public const STATUS_CONTACTED = 'contacted';
    public const STATUS_CLOSED = 'closed';

    public const STATUSES = [
        self::STATUS_NEW => 'جديد',
        self::STATUS_FOLLOWING => 'قيد المتابعة',
        self::STATUS_CONTACTED => 'تم التواصل',
        self::STATUS_CLOSED => 'مغلق',
    ];

    protected $guarded = [];

    protected static function booted(): void
    {
        // Help requests may count as client contacts (ClientClassifier, admin setting).
        static::saved(fn () => \App\Services\ServiceRequests\ClientClassifier::forget());
        static::deleted(fn () => \App\Services\ServiceRequests\ClientClassifier::forget());
    }

    public function getRouteKeyName(): string
    {
        return 'number';
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function service()
    {
        return $this->belongsTo(SubCategory::class, 'sub_category_id');
    }

    public function assignee()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function notes()
    {
        return $this->hasMany(HelpRequestNote::class)->orderByDesc('id');
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    public function serviceLabel(): string
    {
        return $this->service_name ?: 'استفسار عام';
    }

    /** wa.me link to the client's WhatsApp (digits only, international format). */
    public function whatsappUrl(?string $text = null): string
    {
        return 'https://wa.me/' . preg_replace('/\D/', '', $this->whatsapp) . ($text ? '?text=' . rawurlencode($text) : '');
    }
}
