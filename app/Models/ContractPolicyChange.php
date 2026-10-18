<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Who changed «سياسة المشاريع» and what (before → after). */
class ContractPolicyChange extends Model
{
    public const UPDATED_AT = null;

    protected $guarded = [];

    protected $casts = ['changes' => 'array', 'created_at' => 'datetime'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
