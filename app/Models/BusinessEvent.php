<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Audit log of a company request or project: who did what and when; `internal` rows are the team's notes. */
class BusinessEvent extends Model
{
    public const UPDATED_AT = null;

    protected $guarded = [];

    protected $casts = ['created_at' => 'datetime', 'internal' => 'boolean'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function subject()
    {
        return $this->morphTo();
    }
}
