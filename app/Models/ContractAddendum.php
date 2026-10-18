<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Service 3: a change to the agreed stages after the work started, approved by both sides. */
class ContractAddendum extends Model
{
    public const STATUS_PROPOSED = 'proposed';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_CANCELLED = 'cancelled';

    public const STATUSES = [
        self::STATUS_PROPOSED => 'بانتظار موافقة الطرف الآخر',
        self::STATUS_APPROVED => 'تمت الموافقة وطُبّق',
        self::STATUS_REJECTED => 'رُفض',
        self::STATUS_CANCELLED => 'سُحب',
    ];

    protected $table = 'contract_addenda';

    protected $guarded = [];

    protected $casts = [
        'changes' => 'array',
        'total_before' => 'decimal:2',
        'total_after' => 'decimal:2',
        'responded_at' => 'datetime',
    ];

    public function contract()
    {
        return $this->belongsTo(Contract::class);
    }

    public function proposer()
    {
        return $this->belongsTo(User::class, 'proposed_by');
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    public function isOpen(): bool
    {
        return $this->status === self::STATUS_PROPOSED;
    }

    /** One readable line per change, for the page, the log and notifications. */
    public function lines(): array
    {
        $stages = $this->contract->stages->keyBy('id');

        return array_map(function (array $c) use ($stages) {
            $name = isset($c['stage_id']) ? '«' . ($stages[$c['stage_id']]->title ?? 'مرحلة') . '»' : '«' . ($c['title'] ?? '') . '»';

            return match ($c['type']) {
                'add' => 'إضافة مرحلة ' . $name . ': ' . \App\Support\Money::format($c['amount']) . ' · ' . $c['duration_days'] . ' يوم',
                'remove' => 'حذف المرحلة ' . $name,
                default => 'تعديل المرحلة ' . $name . ': ' . implode('، ', array_filter([
                    isset($c['title']) ? 'العنوان «' . $c['title'] . '»' : null,
                    isset($c['amount']) ? 'المبلغ ' . \App\Support\Money::format($c['amount']) : null,
                    isset($c['duration_days']) ? 'المدة ' . $c['duration_days'] . ' يوم' : null,
                    array_key_exists('revisions', $c) ? 'التعديلات ' . ($c['revisions'] === null ? 'غير محدود' : $c['revisions']) : null,
                    isset($c['deliverable']) ? 'ما يُسلَّم' : null,
                    isset($c['acceptance']) ? 'معيار القبول' : null,
                ])),
            };
        }, $this->getAttribute('changes') ?? []); // not $this->changes: Eloquent keeps its own $changes property
    }
}
