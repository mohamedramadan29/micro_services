<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** «بانتظار العميل»: the stage clock stops until the client sends what the freelancer asked for. */
class ContractPause extends Model
{
    public const STATUS_ACTIVE = 'active';
    public const STATUS_RESUMED = 'resumed';
    public const STATUS_OBJECTED = 'objected';

    /** Client decisions 2026-10-16: reminder after 3 days, the admin after 7. */
    public const REMINDER_DAYS = 3;
    public const MAX_DAYS = 7;

    protected $guarded = [];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'reminder_sent_at' => 'datetime',
        'escalated_at' => 'datetime',
    ];

    public function stage()
    {
        return $this->belongsTo(ContractStage::class, 'contract_stage_id');
    }

    public function files()
    {
        return $this->hasMany(ContractDeliveryFile::class);
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_ACTIVE => 'العدّاد متوقف بانتظار العميل',
            self::STATUS_RESUMED => 'استؤنف العدّاد',
            self::STATUS_OBJECTED => 'اعترض العميل، أُحيل للإدارة',
            default => $this->status,
        };
    }
}
