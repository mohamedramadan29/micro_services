<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * A company's request (client file «قسم الشركات»): the free path (long text + phone, no account) or the guided
 * form (answers snapshot). Both end in the same admin inbox; `source` is kept. The team quotes a price for the
 * request, the client (or the admin for him) accepts, payment is online or recorded by the admin, then it becomes
 * a BusinessProject.
 */
class BusinessRequest extends Model
{
    public const SOURCE_FREE = 'free';
    public const SOURCE_GUIDED = 'guided';
    public const SOURCES = [self::SOURCE_FREE => 'طلب حر', self::SOURCE_GUIDED => 'نموذج موجّه'];

    public const STATUS_NEW = 'new';
    public const STATUS_CONTACTING = 'contacting';
    public const STATUS_AWAITING = 'awaiting';
    public const STATUS_QUOTED = 'quoted';
    public const STATUS_ACCEPTED = 'accepted';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_CONVERTED = 'converted';

    public const STATUSES = [
        self::STATUS_NEW => 'جديد',
        self::STATUS_CONTACTING => 'جارٍ التواصل',
        self::STATUS_AWAITING => 'بانتظار تفاصيل',
        self::STATUS_QUOTED => 'أُرسل عرض السعر',
        self::STATUS_ACCEPTED => 'مقبول',
        self::STATUS_REJECTED => 'مرفوض',
        self::STATUS_CONVERTED => 'محوّل لمشروع',
    ];

    /** Statuses the team sets by hand (the others follow the quote and the conversion). */
    public const MANUAL_STATUSES = [self::STATUS_NEW, self::STATUS_CONTACTING, self::STATUS_AWAITING, self::STATUS_REJECTED];

    public const PAYMENT_METHODS = ['online' => 'دفع إلكتروني (EasyKash)', 'cash' => 'نقدًا', 'transfer' => 'تحويل بنكي', 'wallet' => 'محفظة / إنستاباي', 'other' => 'أخرى'];

    public const FREE_TEXT_MAX = 20000;

    protected $guarded = [];

    protected $casts = [
        'answers' => 'array',
        'consent_at' => 'datetime',
        'quote_sent_at' => 'datetime',
        'quote_decided_at' => 'datetime',
        'quote_due_at' => 'date',
        'paid_at' => 'datetime',
    ];

    public function getRouteKeyName(): string
    {
        return 'number';
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function assignee()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function project()
    {
        return $this->belongsTo(BusinessProject::class, 'business_project_id');
    }

    public function files()
    {
        return $this->hasMany(BusinessRequestFile::class);
    }

    public function events()
    {
        return $this->morphMany(BusinessEvent::class, 'subject')->orderByDesc('id');
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    public function sourceLabel(): string
    {
        return self::SOURCES[$this->source] ?? $this->source;
    }

    public function sizeLabel(): string
    {
        return BusinessCompany::SIZES[$this->company_size] ?? '—';
    }

    public function displayName(): string
    {
        return $this->company_name ?: ($this->contact_name ?: ($this->user?->name ?? 'زائر'));
    }

    public function hasQuote(): bool
    {
        return $this->quote_amount !== null && $this->quote_sent_at !== null;
    }

    public function isPaid(): bool
    {
        return $this->payment_status === 'paid';
    }

    /** The client can accept / reject while a quote is waiting for him. */
    public function awaitsClientDecision(): bool
    {
        return $this->status === self::STATUS_QUOTED && $this->hasQuote();
    }

    public function paymentMethodLabel(): string
    {
        return self::PAYMENT_METHODS[$this->payment_method] ?? '—';
    }

    /** wa.me link to the phone (digits only). */
    public function whatsappUrl(?string $text = null): string
    {
        return 'https://wa.me/' . preg_replace('/\D/', '', $this->phone) . ($text ? '?text=' . rawurlencode($text) : '');
    }
}
