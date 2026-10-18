<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** One balance change made by the request system (user_id null = the platform). */
class WalletTransaction extends Model
{
    public const TYPE_ESCROW_IN = 'escrow_in';
    public const TYPE_RELEASE = 'release';
    public const TYPE_COMMISSION = 'commission';
    public const TYPE_REFUND = 'refund';

    protected $guarded = [];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function stage()
    {
        return $this->belongsTo(ContractStage::class, 'contract_stage_id');
    }

    public function payment()
    {
        return $this->belongsTo(RequestPayment::class, 'request_payment_id');
    }
}
