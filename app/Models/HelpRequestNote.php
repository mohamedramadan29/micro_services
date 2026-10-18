<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Internal team note on a help request (never shown to the client). */
class HelpRequestNote extends Model
{
    public const UPDATED_AT = null;

    protected $guarded = [];

    protected $casts = ['created_at' => 'datetime'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
