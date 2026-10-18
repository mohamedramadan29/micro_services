<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** One version of a contract's agreement (service 3). */
class ContractAgreement extends Model
{
    public const STATUS_PROPOSED = 'proposed';
    public const STATUS_CHANGES_REQUESTED = 'changes_requested';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_SUPERSEDED = 'superseded';

    public const STATUSES = [
        self::STATUS_PROPOSED => 'بانتظار موافقة العميل',
        self::STATUS_CHANGES_REQUESTED => 'طلب العميل تعديلًا',
        self::STATUS_APPROVED => 'موافَق عليه من الطرفين',
        self::STATUS_SUPERSEDED => 'نسخة سابقة',
    ];

    /** Revisions per stage when the freelancer does not choose (client decision 2026-10-16). */
    public const DEFAULT_REVISIONS = 2;

    public const MAX_STAGES = 10;

    /**
     * The stages a new proposal starts from: a stage template of the admin (first one by
     * default), splitting the chosen offer's price and days by each stage's share.
     * The freelancer edits, adds or removes them freely.
     */
    public static function defaultStages(float $price, int $days, ?int $template = null): array
    {
        $templates = \App\Services\Contracts\ContractPolicy::templates();
        $plan = $templates[$template ?? 0]['stages'] ?? $templates[0]['stages'];
        $totalShare = max(1, array_sum(array_map(fn ($s) => (float) ($s['share'] ?? 0), $plan)));
        $days = max(count($plan), $days);
        $revisions = \App\Services\Contracts\ContractPolicy::number('default_revisions');
        $stages = [];
        $amountLeft = round($price, 2);
        $daysLeft = $days;
        foreach (array_values($plan) as $index => $step) {
            $share = (float) ($step['share'] ?? 0) / $totalShare;
            $last = $index === count($plan) - 1;
            $amount = $last ? $amountLeft : round($price * $share, 2);
            $stageDays = $last ? $daysLeft : max(1, (int) round($days * $share));
            $amountLeft = round($amountLeft - $amount, 2);
            $daysLeft -= $stageDays;
            $stages[] = ['title' => $step['title'], 'deliverable' => $step['deliverable'] ?? '', 'acceptance' => $step['acceptance'] ?? '', 'duration_days' => max(1, $stageDays),
                'amount' => $amount > 0 ? $amount : '', 'revisions' => $revisions];
        }

        return $stages;
    }

    protected $guarded = [];

    protected $casts = [
        'stages' => 'array',
        'total' => 'decimal:2',
        'freelancer_approved_at' => 'datetime',
        'client_approved_at' => 'datetime',
    ];

    public function contract()
    {
        return $this->belongsTo(Contract::class);
    }

    public function proposer()
    {
        return $this->belongsTo(User::class, 'proposed_by');
    }

    public function totalDays(): int
    {
        return (int) collect($this->stages)->sum('duration_days');
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }
}
