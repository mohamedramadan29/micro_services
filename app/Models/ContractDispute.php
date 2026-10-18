<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** A dispute on a contract (service 3); the stage money stays frozen until the admin decides. */
class ContractDispute extends Model
{
    public const STATUS_OPEN = 'open';                     // waiting for the other side (3 days)
    public const STATUS_AWAITING = 'awaiting_decision';    // answered or time is up: the admin decides
    public const STATUS_DECIDED = 'decided';
    public const STATUS_WITHDRAWN = 'withdrawn';

    public const STATUSES = [
        self::STATUS_OPEN => 'بانتظار رد الطرف الآخر',
        self::STATUS_AWAITING => 'بانتظار قرار الإدارة',
        self::STATUS_DECIDED => 'حسمته الإدارة',
        self::STATUS_WITHDRAWN => 'سُحب',
    ];

    public const OPEN_STATUSES = [self::STATUS_OPEN, self::STATUS_AWAITING];

    public const RESPOND_DAYS = 3;

    /** Client decision 2026-10-16. */
    public const REASONS = [
        'out_of_scope' => 'تسليم خارج النطاق المتفق عليه',
        'big_delay' => 'تأخر كبير',
        'bad_quality' => 'جودة غير مقبولة',
        'no_response' => 'الطرف الآخر لا يرد',
        'revisions_used' => 'انتهت التعديلات المتفق عليها',
        'other' => 'سبب آخر',
    ];

    public const DECISIONS = [
        'continue' => 'متابعة العمل',
        'revision' => 'تعديل محدد بمهلة',
        'release' => 'تحرير المبلغ للمستقل',
        'refund' => 'استرجاع المبلغ لرصيد العميل',
        'split' => 'تقسيم المبلغ بنسبة',
        'change_freelancer' => 'تغيير المستقل',
    ];

    protected $guarded = [];

    protected $casts = [
        'respond_due_at' => 'datetime',
        'responded_at' => 'datetime',
        'decided_at' => 'datetime',
        'revision_due' => 'date',
        'proposed' => 'array',
    ];

    public function contract()
    {
        return $this->belongsTo(Contract::class);
    }

    public function stage()
    {
        return $this->belongsTo(ContractStage::class, 'contract_stage_id');
    }

    public function opener()
    {
        return $this->belongsTo(User::class, 'opened_by');
    }

    public function files()
    {
        return $this->hasMany(ContractDeliveryFile::class);
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
