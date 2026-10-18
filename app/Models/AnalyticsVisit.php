<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** One visit (a session of an anonymous visitor), with where it came from. */
class AnalyticsVisit extends Model
{
    public $timestamps = false;

    protected $guarded = [];

    protected $casts = ['started_at' => 'datetime', 'last_seen_at' => 'datetime'];

    public function events()
    {
        return $this->hasMany(AnalyticsEvent::class, 'visit_id')->orderBy('id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function sourceLabel(): string
    {
        return \App\Services\Analytics\Tracker::SOURCES[$this->source] ?? $this->source;
    }

    public function stepLabel(): string
    {
        return \App\Services\Analytics\Tracker::FUNNEL[$this->last_step] ?? 'تصفّح فقط';
    }
}
