<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RequestPayment extends Model
{
    public const STATUS_PENDING = 'pending';     // checkout started, not paid
    public const STATUS_PAID = 'paid';           // held by the site (escrow)
    public const STATUS_RELEASED = 'released';   // paid out to the freelancer
    public const STATUS_REFUNDED = 'refunded';
    public const STATUS_CONFLICT = 'conflict';   // paid after another offer was already paid: needs a refund
    public const STATUS_CANCELLED = 'cancelled'; // checkout abandoned or replaced

    public const STATUSES = [
        self::STATUS_PENDING => 'بانتظار الدفع',
        self::STATUS_PAID => 'محجوز لدى نفّذها',
        self::STATUS_RELEASED => 'تم تحويله للمستقل',
        self::STATUS_REFUNDED => 'تم استرجاعه',
        self::STATUS_CONFLICT => 'دفعة مكررة تحتاج استرجاعًا',
        self::STATUS_CANCELLED => 'ملغي',
    ];

    protected $guarded = [];

    protected $casts = [
        'amount' => 'decimal:2',
        'commission_rate' => 'decimal:2',
        'commission_amount' => 'decimal:2',
        'freelancer_amount' => 'decimal:2',
        'refunded_amount' => 'decimal:2',
        'paid_at' => 'datetime',
        'released_at' => 'datetime',
    ];

    public function onlinePayment()
    {
        return $this->belongsTo(OnlinePayment::class);
    }

    public function request()
    {
        return $this->belongsTo(ServiceRequest::class, 'service_request_id');
    }

    public function offer()
    {
        return $this->belongsTo(RequestOffer::class, 'request_offer_id');
    }

    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function freelancer()
    {
        return $this->belongsTo(User::class, 'freelancer_id');
    }

    /** The price-difference change this payment pays for (null = the first payment). */
    public function change()
    {
        return $this->belongsTo(ServiceRequestChange::class, 'service_request_change_id');
    }

    /** What is still held or was paid out: the amount minus what went back to the client. */
    public function netAmount(): float
    {
        return round((float) $this->amount - (float) $this->refunded_amount, 2);
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }
}
