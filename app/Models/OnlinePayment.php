<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * One payment at EasyKash (id = customerReference). `purpose` picks the class
 * in OnlinePaymentService::PURPOSES that acts on it once paid; `payload`
 * keeps what that class needs (ids, prices and rates fixed at checkout).
 */
class OnlinePayment extends Model
{
    public const PURPOSE_REQUEST = 'request';
    public const PURPOSE_SERVICE_ORDER = 'service_order';
    public const PURPOSE_PRODUCT_ORDER = 'product_order';
    public const PURPOSE_PACKAGE = 'package';
    public const PURPOSE_COURSE = 'course';
    public const PURPOSE_BALANCE = 'balance';
    public const PURPOSE_CONTRACT_STAGE = 'contract_stage'; // service 3: funding contract stages
    public const PURPOSE_BUSINESS = 'business_quote'; // «نفّذها للأعمال»: a company pays its accepted quote

    public const STATUS_PENDING = 'pending';     // sent to EasyKash, not paid (yet)
    public const STATUS_PAID = 'paid';           // confirmed by EasyKash and acted on
    public const STATUS_FAILED = 'failed';       // failed, expired or cancelled at EasyKash
    public const STATUS_CANCELLED = 'cancelled'; // replaced or could not start on our side

    protected $guarded = [];

    protected $hidden = ['return_token'];

    protected $casts = [
        'payload' => 'array',
        'amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function data(string $key, $default = null)
    {
        return data_get($this->payload, $key, $default);
    }

    public function isPaid(): bool
    {
        return $this->status === self::STATUS_PAID;
    }
}
