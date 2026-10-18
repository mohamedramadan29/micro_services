<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** A request to change the contract's freelancer (always decided by the admin). */
class ContractFreelancerChange extends Model
{
    public const KINDS = [
        'client' => 'طلب العميل',
        'withdrawal' => 'انسحاب المستقل',
        'admin' => 'قرار الإدارة',
    ];

    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';

    protected $guarded = [];

    protected $casts = ['decided_at' => 'datetime', 'proposed' => 'array'];

    public function contract()
    {
        return $this->belongsTo(Contract::class);
    }

    public function requester()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function leaving()
    {
        return $this->belongsTo(User::class, 'leaving_freelancer_id');
    }

    public function continuation()
    {
        return $this->belongsTo(ServiceRequest::class, 'continuation_request_id');
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING => 'بانتظار قرار الإدارة',
            self::STATUS_APPROVED => 'اعتمدته الإدارة',
            self::STATUS_REJECTED => 'رفضته الإدارة',
            default => $this->status,
        };
    }
}
