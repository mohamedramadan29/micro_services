<?php

namespace App\Models;

use App\Models\admin\SubCategory;
use Illuminate\Database\Eloquent\Model;

class AnalyticsEvent extends Model
{
    public const UPDATED_AT = null;

    protected $guarded = [];

    protected $casts = ['meta' => 'array', 'created_at' => 'datetime'];

    public function service()
    {
        return $this->belongsTo(SubCategory::class, 'sub_category_id');
    }

    public function label(): string
    {
        return \App\Services\Analytics\Tracker::EVENTS[$this->type] ?? $this->type;
    }
}
