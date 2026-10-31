<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** A task of a company project (kanban: المطلوب → قيد التنفيذ → للمراجعة → جاهز). */
class BusinessTask extends Model
{
    public const STATUS_TODO = 'todo';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_REVIEW = 'review';
    public const STATUS_DONE = 'done';

    public const STATUSES = [
        self::STATUS_TODO => 'المطلوب',
        self::STATUS_IN_PROGRESS => 'قيد التنفيذ',
        self::STATUS_REVIEW => 'للمراجعة',
        self::STATUS_DONE => 'جاهز',
    ];

    public const PRIORITIES = ['low' => 'منخفضة', 'normal' => 'عادية', 'high' => 'عالية'];

    protected $guarded = [];

    protected $casts = ['due_at' => 'date', 'completed_at' => 'datetime'];

    public function project()
    {
        return $this->belongsTo(BusinessProject::class, 'business_project_id');
    }

    public function assignee()
    {
        return $this->belongsTo(User::class, 'assignee_id');
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    public function isLate(): bool
    {
        return $this->due_at && $this->status !== self::STATUS_DONE && $this->due_at->endOfDay()->isPast();
    }
}
