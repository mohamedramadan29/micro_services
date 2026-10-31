<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * One version of a deliverable sent to the client for approval (the approval gate, file page 9): approved, or
 * changes requested with a comment → the team sends a new version (same root, version + 1).
 */
class BusinessDelivery extends Model
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_CHANGES = 'changes_requested';

    public const STATUSES = [self::STATUS_PENDING => 'بانتظار الاعتماد', self::STATUS_APPROVED => 'معتمد', self::STATUS_CHANGES => 'مطلوب تعديل'];

    protected $guarded = [];

    protected $casts = ['links' => 'array', 'reviewed_at' => 'datetime'];

    public function project()
    {
        return $this->belongsTo(BusinessProject::class, 'business_project_id');
    }

    public function task()
    {
        return $this->belongsTo(BusinessTask::class, 'business_task_id');
    }

    public function files()
    {
        return $this->hasMany(BusinessDeliveryFile::class);
    }

    public function submitter()
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    public function rootId(): int
    {
        return $this->root_id ?? $this->id;
    }
}
