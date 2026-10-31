<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** A company project executed by the Nafizha team (from an accepted request): tasks, deliveries, approvals. */
class BusinessProject extends Model
{
    public const STATUS_ACTIVE = 'active';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';

    public const STATUSES = [self::STATUS_ACTIVE => 'جارٍ', self::STATUS_COMPLETED => 'مكتمل', self::STATUS_CANCELLED => 'ملغى'];

    protected $guarded = [];

    protected $casts = ['due_at' => 'date', 'completed_at' => 'datetime', 'amount' => 'decimal:2'];

    public function getRouteKeyName(): string
    {
        return 'number';
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function request()
    {
        return $this->hasOne(BusinessRequest::class);
    }

    public function tasks()
    {
        return $this->hasMany(BusinessTask::class)->orderBy('sort_order')->orderBy('id');
    }

    public function deliveries()
    {
        return $this->hasMany(BusinessDelivery::class)->orderByDesc('id');
    }

    public function events()
    {
        return $this->morphMany(BusinessEvent::class, 'subject')->orderByDesc('id');
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    /** Share of done tasks (0–100), for the progress bars. */
    public function progress(): int
    {
        $tasks = $this->relationLoaded('tasks') ? $this->tasks : $this->tasks()->get();

        return $tasks->isEmpty() ? 0 : (int) round($tasks->where('status', BusinessTask::STATUS_DONE)->count() * 100 / $tasks->count());
    }
}
