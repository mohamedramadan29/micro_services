<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** One stage of a contract and where its money is (see App\Services\Contracts\ContractLedger). */
class ContractStage extends Model
{
    public const STATUS_UNFUNDED = 'unfunded';
    public const STATUS_FUNDING = 'funding';   // an EasyKash payment for it is open
    public const STATUS_FUNDED = 'funded';     // «يبقى المبلغ لدى نفّذها حتى تعتمد المرحلة»
    public const STATUS_RELEASED = 'released';
    public const STATUS_REFUNDED = 'refunded';
    public const STATUS_SPLIT = 'split';

    public const STATUSES = [
        self::STATUS_UNFUNDED => 'غير مموّلة',
        self::STATUS_FUNDING => 'بانتظار الدفع',
        self::STATUS_FUNDED => 'المبلغ لدى نفّذها حتى تعتمد المرحلة',
        self::STATUS_RELEASED => 'اعتُمدت وحُوّلت للمستقل',
        self::STATUS_REFUNDED => 'أُعيد المبلغ لرصيد العميل',
        self::STATUS_SPLIT => 'قُسّم المبلغ',
    ];

    protected $guarded = [];

    /**
     * Late enough now for the client to ask for another freelancer: running, not settled,
     * not paused or waiting for review, and past its deadline by the policy's days.
     */
    public function scopeLateForChange($query)
    {
        return $query->whereIn('status', [self::STATUS_FUNDED])
            ->whereNotNull('late_escalated_at')
            ->where('due_at', '<=', now()->subDays(\App\Services\Contracts\ContractPolicy::number('late_change_days')))
            ->whereDoesntHave('deliveries', fn ($q) => $q->where('status', ContractDelivery::STATUS_SUBMITTED))
            ->whereDoesntHave('pauses', fn ($q) => $q->where('status', ContractPause::STATUS_ACTIVE));
    }

    /** Past its deadline and still the freelancer's turn (not paused, not waiting for the client's review). */
    public function scopeLateNow($query)
    {
        return $query->where('status', self::STATUS_FUNDED)->whereNotNull('due_at')->where('due_at', '<', now())
            ->whereDoesntHave('deliveries', fn ($q) => $q->where('status', ContractDelivery::STATUS_SUBMITTED))
            ->whereDoesntHave('pauses', fn ($q) => $q->where('status', ContractPause::STATUS_ACTIVE));
    }

    protected $casts = [
        'amount' => 'decimal:2',
        'commission_rate' => 'decimal:2',
        'released_amount' => 'decimal:2',
        'commission_amount' => 'decimal:2',
        'freelancer_amount' => 'decimal:2',
        'refunded_amount' => 'decimal:2',
        'duration_days' => 'integer',
        'revisions' => 'integer',
        'revisions_used' => 'integer',
        'started_at' => 'datetime',
        'due_at' => 'datetime',
        'funding_requested_at' => 'datetime',
        'delivered_at' => 'datetime',
        'due_reminder_sent_at' => 'datetime',
        'late_notified_at' => 'datetime',
        'late_escalated_at' => 'datetime',
        'funded_at' => 'datetime',
        'settled_at' => 'datetime',
    ];

    public function contract()
    {
        return $this->belongsTo(Contract::class);
    }

    public function deliveries()
    {
        return $this->hasMany(ContractDelivery::class)->latest('id');
    }

    public function pauses()
    {
        return $this->hasMany(ContractPause::class)->latest('id');
    }

    public function activePause(): ?ContractPause
    {
        return $this->pauses()->where('status', ContractPause::STATUS_ACTIVE)->first();
    }

    /** Whole days past the deadline (0 when not late). */
    public function daysLate(): int
    {
        return $this->due_at && $this->due_at->isPast() && ! $this->isSettled() ? (int) floor($this->due_at->diffInHours(now()) / 24) + 1 : 0;
    }

    /** Revisions the client may still ask for (null = unlimited). */
    public function revisionsLeft(): ?int
    {
        return $this->revisions === null ? null : max(0, $this->revisions - $this->revisions_used);
    }

    public function isSettled(): bool
    {
        return in_array($this->status, [self::STATUS_RELEASED, self::STATUS_REFUNDED, self::STATUS_SPLIT], true);
    }

    public function releasedTo()
    {
        return $this->belongsTo(User::class, 'released_to');
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }
}
