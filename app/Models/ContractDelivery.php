<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** One delivery of a stage by the freelancer, and the client's answer. */
class ContractDelivery extends Model
{
    public const STATUS_SUBMITTED = 'submitted';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REVISION = 'revision_requested';

    public const STATUSES = [
        self::STATUS_SUBMITTED => 'بانتظار مراجعة العميل',
        self::STATUS_APPROVED => 'اعتمدها العميل',
        self::STATUS_REVISION => 'طلب العميل تعديلًا',
    ];

    /** Days the client has to review (client decision 2026-10-16: 5 days, reminder after 3, then the admin). */
    public const REVIEW_DAYS = 5;

    /** Files of deliveries and evidence (client decision 2026-10-16). */
    public const FILE_MAX_MB = 20;
    public const MAX_FILES = 10;
    public const MAX_LINKS = 5;
    public const FILE_EXTENSIONS = ['jpg', 'jpeg', 'png', 'webp', 'pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'zip', 'rar'];

    protected $guarded = [];

    protected $casts = [
        'links' => 'array',
        'review_due_at' => 'datetime',
        'reviewed_at' => 'datetime',
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
        return self::STATUSES[$this->status] ?? $this->status;
    }
}
