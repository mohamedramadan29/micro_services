<?php

namespace App\Models;

use App\Models\admin\SubCategory;
use Illuminate\Database\Eloquent\Model;

/** Admin rule: an engine result code → the site service that fixes it. */
class SiteTestRule extends Model
{
    protected $guarded = [];

    protected $casts = ['active' => 'boolean'];

    public function service()
    {
        return $this->belongsTo(SubCategory::class, 'sub_category_id');
    }
}
