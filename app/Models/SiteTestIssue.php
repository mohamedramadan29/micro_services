<?php

namespace App\Models;

use App\Models\admin\SubCategory;
use Illuminate\Database\Eloquent\Model;

/** One problem found by the engine (source tool) or noted by the team (source team). */
class SiteTestIssue extends Model
{
    public const PRIORITIES = [
        'high' => 'عالية',
        'medium' => 'متوسطة',
        'team' => 'مراجعة الفريق',
    ];

    public const PRIORITY_ORDER = ['high' => 0, 'medium' => 1, 'team' => 2];

    protected $guarded = [];

    protected $casts = ['shown' => 'boolean'];

    public function service()
    {
        return $this->belongsTo(SubCategory::class, 'sub_category_id');
    }

    public function priorityLabel(): string
    {
        return self::PRIORITIES[$this->priority] ?? $this->priority;
    }
}
