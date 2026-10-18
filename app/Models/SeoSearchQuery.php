<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** A Google search query that showed the site (Search Console, last 28 days). */
class SeoSearchQuery extends Model
{
    public $timestamps = false;

    protected $guarded = [];

    protected $casts = ['synced_at' => 'datetime', 'ctr' => 'decimal:4', 'position' => 'decimal:2'];
}
