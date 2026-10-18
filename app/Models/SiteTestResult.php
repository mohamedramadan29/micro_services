<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** The engine's raw answer for one device, kept as evidence. */
class SiteTestResult extends Model
{
    public $timestamps = false;

    protected $guarded = [];

    protected $casts = ['ok' => 'boolean', 'fetched_at' => 'datetime'];
}
