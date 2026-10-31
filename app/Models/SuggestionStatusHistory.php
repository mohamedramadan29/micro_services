<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Audit log of a suggestion: creation, status changes (with reason), assignment, priority, duplicate links. */
class SuggestionStatusHistory extends Model
{
    public const UPDATED_AT = null;

    protected $table = 'suggestion_status_history';

    protected $guarded = [];

    protected $casts = ['created_at' => 'datetime'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
