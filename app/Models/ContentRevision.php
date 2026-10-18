<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** What visitors saw right after a publish (or a rollback) of one content page: change log + rollback. */
class ContentRevision extends Model
{
    public const UPDATED_AT = null;

    protected $guarded = [];

    protected $casts = ['snapshot' => 'array', 'created_at' => 'datetime'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
