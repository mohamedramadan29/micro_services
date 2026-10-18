<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RequestOffer extends Model
{
    public const STATUS_NEW = 'new';
    public const STATUS_VIEWED = 'viewed';
    public const STATUS_IN_DISCUSSION = 'in_discussion';
    public const STATUS_ACCEPTED = 'accepted';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_WITHDRAWN = 'withdrawn';

    public const STATUSES = [
        self::STATUS_NEW => 'وصل حديثًا',
        self::STATUS_VIEWED => 'تمت مشاهدته',
        self::STATUS_IN_DISCUSSION => 'قيد النقاش',
        self::STATUS_ACCEPTED => 'تم اختياره',
        self::STATUS_REJECTED => 'لم يُختر',
        self::STATUS_WITHDRAWN => 'سُحب',
    ];

    /** Statuses of an offer that is still in play on an open request. */
    public const ACTIVE_STATUSES = [self::STATUS_NEW, self::STATUS_VIEWED, self::STATUS_IN_DISCUSSION];

    public const MAX_LINKS = 3;
    public const MAX_FILES = 3;
    public const FILE_MAX_MB = 5;
    public const FILE_EXTENSIONS = ['jpg', 'jpeg', 'png', 'webp', 'pdf', 'zip'];

    protected $guarded = [];

    /** Same defaults as the table, so a freshly created offer knows its status. */
    protected $attributes = [
        'status' => self::STATUS_NEW,
        'is_saved' => false,
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'price' => 'decimal:2',
        'portfolio_links' => 'array',
        'is_saved' => 'boolean',
        'viewed_at' => 'datetime',
    ];

    /** The client edited the request after this offer was sent (the freelancer may want to update it). */
    public function requestEditedSince(): bool
    {
        $edited = $this->request?->edited_at;

        return $edited !== null && $edited->gt($this->submitted_at ?? $this->created_at);
    }

    public function request()
    {
        return $this->belongsTo(ServiceRequest::class, 'service_request_id');
    }

    public function freelancer()
    {
        return $this->belongsTo(User::class, 'freelancer_id');
    }

    public function files()
    {
        return $this->hasMany(RequestOfferFile::class);
    }

    public function messages()
    {
        return $this->hasMany(OfferMessage::class)->orderBy('created_at')->orderBy('id');
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', self::ACTIVE_STATUSES);
    }

    public function isActive(): bool
    {
        return in_array($this->status, self::ACTIVE_STATUSES, true);
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    /** Whether $user is one of the two parties of this offer. */
    public function involves(User $user): bool
    {
        return (int) $user->id === (int) $this->freelancer_id
            || (int) $user->id === (int) $this->request->user_id;
    }

    /** The client opened the offers: new offers become viewed. */
    public function markViewed(): void
    {
        if ($this->status === self::STATUS_NEW) {
            $this->update(['status' => self::STATUS_VIEWED, 'viewed_at' => now()]);
        }
    }

    /**
     * How well the offer fits the request, for the "best match" sort:
     * price inside the client's budget, delivery before the client's date,
     * then a cheaper price as a tiebreak.
     */
    public function matchScore(ServiceRequest $request): float
    {
        $score = 0;
        $price = (float) $this->price;
        if ($request->budget_min !== null || $request->budget_max !== null) {
            $min = $request->budget_min !== null ? (float) $request->budget_min : 0;
            $max = $request->budget_max !== null ? (float) $request->budget_max : PHP_FLOAT_MAX;
            if ($price >= $min && $price <= $max) {
                $score += 2;
            } elseif ($price < $min) {
                $score += 1;
            }
        }
        if ($request->deadline && $this->created_at && $this->created_at->copy()->addDays($this->duration_days)->lte($request->deadline->copy()->endOfDay())) {
            $score += 1;
        }
        $score += (int) ($this->freelancer?->rate ?? 0) / 10;

        return $score;
    }
}
