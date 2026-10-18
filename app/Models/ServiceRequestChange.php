<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** A change to a request after work started; the freelancer approves it (see RequestChangeService). */
class ServiceRequestChange extends Model
{
    public const STATUS_PENDING = 'pending';                   // waiting for the freelancer
    public const STATUS_AWAITING_PAYMENT = 'awaiting_payment'; // approved, the client pays the difference
    public const STATUS_APPLIED = 'applied';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_CANCELLED = 'cancelled';

    public const STATUSES = [
        self::STATUS_PENDING => 'بانتظار موافقة المستقل',
        self::STATUS_AWAITING_PAYMENT => 'وافق المستقل، بانتظار دفع الفرق',
        self::STATUS_APPLIED => 'مُعتمد',
        self::STATUS_REJECTED => 'رفضه المستقل',
        self::STATUS_CANCELLED => 'أُلغي',
    ];

    /** Statuses that block a second change request. */
    public const OPEN_STATUSES = [self::STATUS_PENDING, self::STATUS_AWAITING_PAYMENT];

    protected $guarded = [];

    protected $casts = [
        'old_deadline' => 'date',
        'new_deadline' => 'date',
        'old_price' => 'decimal:2',
        'new_price' => 'decimal:2',
        'responded_at' => 'datetime',
        'applied_at' => 'datetime',
    ];

    public function request()
    {
        return $this->belongsTo(ServiceRequest::class, 'service_request_id');
    }

    public function requester()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function freelancer()
    {
        return $this->belongsTo(User::class, 'freelancer_id');
    }

    public function payments()
    {
        return $this->hasMany(RequestPayment::class, 'service_request_change_id');
    }

    /** Positive = the client pays more, negative = money goes back to the client's balance. */
    public function difference(): float
    {
        return round((float) $this->new_price - (float) $this->old_price, 2);
    }

    public function isOpen(): bool
    {
        return in_array($this->status, self::OPEN_STATUSES, true);
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }
}
