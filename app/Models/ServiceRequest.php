<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceRequest extends Model
{
    public const PATH_EXECUTE = 'execute';
    public const PATH_EXPLORE = 'explore';

    /** Kinds: a normal request (pay once) or a staged project (service 3 «مشروعك ما بيوقف»). */
    public const KIND_STANDARD = 'standard';
    public const KIND_STAGED = 'staged';

    public const STATUS_OPEN = 'open';        // published, open for offers
    public const STATUS_INQUIRY = 'inquiry';  // private price inquiry, admin only
    public const STATUS_IN_PROGRESS = 'in_progress'; // offer chosen and paid (held by the site)
    public const STATUS_COMPLETED = 'completed';     // client confirmed, freelancer paid
    public const STATUS_NOT_COMPLETED = 'not_completed'; // closed without being carried out (see closed_reason)

    public const STATUSES = [
        self::STATUS_OPEN => 'مفتوح للعروض',
        self::STATUS_INQUIRY => 'استفسار أسعار',
        self::STATUS_IN_PROGRESS => 'قيد التنفيذ',
        self::STATUS_COMPLETED => 'تم التنفيذ',
        self::STATUS_NOT_COMPLETED => 'لم يتم التنفيذ',
    ];

    /** Where the request came from (client file «المربعات الخمسة»). */
    public const SOURCE_GUIDED = 'guided';
    public const SOURCE_DIRECT = 'direct';
    public const SOURCE_SITE_TEST = 'site_test';

    public const SOURCES = [
        self::SOURCE_GUIDED => 'الأسئلة الموجّهة',
        self::SOURCE_DIRECT => 'نشر مباشر',
        self::SOURCE_SITE_TEST => 'فحص الموقع',
    ];

    public function isDirect(): bool
    {
        return $this->source === self::SOURCE_DIRECT;
    }

    /** Why a request ended without being carried out (page 19 of the spec). */
    public const CLOSE_REASONS = [
        'client_cancelled' => 'ألغاه العميل',
        'price_not_agreed' => 'لم يتم الاتفاق على السعر',
        'no_offer_chosen' => 'لم يختر العميل عرضًا',
        'no_offers' => 'لم تصل عروض مناسبة',
        'freelancer_withdrew' => 'انسحب المستقل',
        'freelancer_replaced' => 'تغيّر المستقل (يُستكمل في طلب جديد)',
        'not_feasible' => 'تعذّر التنفيذ',
        'other' => 'سبب آخر',
    ];

    /** Optional preferred contact method (spec step 4). */
    public const CONTACT_METHODS = [
        'whatsapp' => 'واتساب',
        'phone' => 'اتصال هاتفي',
        'email' => 'البريد الإلكتروني',
    ];

    public const FOLLOWUP_NEW = 'new';
    public const FOLLOWUP_IN_PROGRESS = 'in_progress';
    public const FOLLOWUP_RESOLVED = 'resolved';

    public const FOLLOWUP_STATUSES = [
        self::FOLLOWUP_NEW => 'جديد',
        self::FOLLOWUP_IN_PROGRESS => 'قيد المتابعة',
        self::FOLLOWUP_RESOLVED => 'تم الحل',
    ];

    protected $guarded = [];

    /** Optional explanation written to the history with the next status change (not a column). */
    public ?string $statusChangeNote = null;

    /** Client classifications are cached; any change here makes them stale. */
    protected static function booted(): void
    {
        static::saved(fn () => \App\Services\ServiceRequests\ClientClassifier::forget());
        static::deleted(fn () => \App\Services\ServiceRequests\ClientClassifier::forget());

        // Every status change is logged with who made it (spec steps 6, 14 and 19).
        static::created(fn (self $request) => ServiceRequestEvent::record($request, ServiceRequestEvent::TYPE_CREATED, ['to_status' => $request->status]));
        static::updated(function (self $request) {
            if ($request->wasChanged('status')) {
                ServiceRequestEvent::record($request, ServiceRequestEvent::TYPE_STATUS, [
                    'from_status' => $request->getOriginal('status'),
                    'to_status' => $request->status,
                    'note' => trim(implode(' — ', array_filter([
                        $request->status === self::STATUS_NOT_COMPLETED ? $request->closeReasonLabel() : null,
                        $request->statusChangeNote,
                    ]))) ?: null,
                ]);
                $request->statusChangeNote = null;
            }
        });
    }

    public function events()
    {
        return $this->hasMany(ServiceRequestEvent::class)->orderByDesc('id');
    }

    protected $casts = [
        'published_at' => 'datetime',
        'delivered_at' => 'datetime',
        'completed_at' => 'datetime',
        'no_offer_alerted_at' => 'datetime',
        'followup_updated_at' => 'datetime',
        'closed_at' => 'datetime',
        'deadline' => 'date',
        'edited_at' => 'datetime',
        'stopped_project' => 'boolean',
        'agreed_price' => 'decimal:2',
        'budget_min' => 'decimal:2',
        'budget_max' => 'decimal:2',
    ];

    public function getRouteKeyName(): string
    {
        return 'number';
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function form()
    {
        return $this->belongsTo(ServiceForm::class, 'service_form_id');
    }

    public function answers()
    {
        return $this->hasMany(ServiceRequestAnswer::class)->orderBy('sort_order')->orderBy('id');
    }

    public function files()
    {
        return $this->hasMany(ServiceRequestFile::class);
    }

    public function attachments()
    {
        return $this->files()->whereNull('question_id');
    }

    public function offers()
    {
        return $this->hasMany(RequestOffer::class);
    }

    /** Conversation between the client and the site team about this request. */
    public function adminMessages()
    {
        return $this->hasMany(RequestAdminMessage::class)->orderBy('id');
    }

    public function acceptedOffer()
    {
        return $this->belongsTo(RequestOffer::class, 'accepted_offer_id');
    }

    public function payments()
    {
        return $this->hasMany(RequestPayment::class);
    }

    /** The first payment for the chosen offer (price-difference payments are separate rows). */
    public function activePayment(): ?RequestPayment
    {
        return $this->payments()->whereIn('status', [RequestPayment::STATUS_PAID, RequestPayment::STATUS_RELEASED])
            ->whereNull('service_request_change_id')->latest('id')->first();
    }

    public function isStaged(): bool
    {
        return $this->kind === self::KIND_STAGED;
    }

    /** The contract of a staged project, once an offer was chosen. */
    public function contract()
    {
        return $this->hasOne(Contract::class)->latestOfMany();
    }

    /** Changes requested after work started (newest first). */
    public function changes()
    {
        return $this->hasMany(ServiceRequestChange::class)->latest('id');
    }

    public function openChange(): ?ServiceRequestChange
    {
        return $this->changes()->whereIn('status', ServiceRequestChange::OPEN_STATUSES)->first();
    }

    /** The price agreed now: the chosen offer's, or the last approved change's. */
    public function currentPrice(): float
    {
        return (float) ($this->agreed_price ?? $this->acceptedOffer?->price ?? 0);
    }

    /**
     * Money of the chosen offer across all its payments (first payment + paid differences),
     * net of amounts given back after a price decrease.
     *
     * @return array{amount: float, freelancer: float, commission: float, rate: float}
     */
    public function moneyTotals(): array
    {
        $payments = $this->payments()->whereIn('status', [RequestPayment::STATUS_PAID, RequestPayment::STATUS_RELEASED])->get();

        return [
            'amount' => round($payments->sum(fn (RequestPayment $p) => $p->netAmount()), 2),
            'freelancer' => round((float) $payments->sum('freelancer_amount'), 2),
            'commission' => round((float) $payments->sum('commission_amount'), 2),
            'rate' => (float) ($payments->first()?->commission_rate ?? 0),
        ];
    }

    public function isInProgress(): bool
    {
        return $this->status === self::STATUS_IN_PROGRESS;
    }

    public function closedBy()
    {
        return $this->belongsTo(User::class, 'closed_by');
    }

    public function closeReasonLabel(): ?string
    {
        return $this->closed_reason ? (self::CLOSE_REASONS[$this->closed_reason] ?? $this->closed_reason) : null;
    }

    public function followupLabel(): ?string
    {
        // A price inquiry is followed up like a no-offer alert; untouched = «جديد».
        $status = $this->followup_status ?: ($this->isInquiry() ? self::FOLLOWUP_NEW : null);

        return $status ? (self::FOLLOWUP_STATUSES[$status] ?? $status) : null;
    }

    /** An offer arrived after the no-offer alert: the alert is answered. */
    public function resolveNoOfferAlert(): void
    {
        if ($this->no_offer_alerted_at && $this->followup_status !== self::FOLLOWUP_RESOLVED) {
            $this->update([
                'followup_status' => self::FOLLOWUP_RESOLVED,
                'followup_note' => trim(($this->followup_note ? $this->followup_note . "\n" : '') . 'وصل أول عرض ' . now()->format('Y-m-d H:i')),
                'followup_updated_at' => now(),
            ]);
        }
    }

    public function isOpen(): bool
    {
        return $this->status === self::STATUS_OPEN;
    }

    /** Open requests in the services a freelancer works in. */
    public function scopeForFreelancer($query, User $freelancer)
    {
        return $query->where('status', self::STATUS_OPEN)
            ->whereIn('sub_category_id', $freelancer->specializations()->pluck('sub_categories.id'));
    }

    /**
     * A freelancer sees a request when it is open and in one of their
     * specializations, or when they already made an offer on it.
     */
    public function visibleToFreelancer(User $user): bool
    {
        if (! $user->isFreelancer()) {
            return false;
        }
        if ($this->offers()->where('freelancer_id', $user->id)->exists()) {
            return true;
        }

        return $this->isOpen() && $user->specializations()->where('sub_categories.id', $this->sub_category_id)->exists();
    }

    public function isInquiry(): bool
    {
        return $this->status === self::STATUS_INQUIRY;
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    /**
     * Publish a private price inquiry as an open request, keeping the same
     * record (the client does not refill anything).
     */
    public function publish(): void
    {
        $this->update([
            'path' => self::PATH_EXECUTE,
            'status' => self::STATUS_OPEN,
            'published_at' => now(),
        ]);
    }
}
