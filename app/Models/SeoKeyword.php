<?php

namespace App\Models;

use App\Models\admin\SubCategory;
use Illuminate\Database\Eloquent\Model;

/** A tracked keyword (client file «تعديلات», item 05). */
class SeoKeyword extends Model
{
    public const STATUSES = [
        'linked' => 'مربوطة',
        'review' => 'قيد المراجعة',
        'unlinked' => 'غير مربوطة',
    ];

    protected $guarded = [];

    protected $casts = [
        'gsc_ctr' => 'decimal:4',
        'gsc_position' => 'decimal:2',
        'gsc_synced_at' => 'datetime',
    ];

    public function service()
    {
        return $this->belongsTo(SubCategory::class, 'sub_category_id');
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    /** Keywords are matched to Google queries ignoring case and extra spaces. */
    public static function normalize(string $keyword): string
    {
        return mb_strtolower(trim(preg_replace('/\s+/u', ' ', $keyword)));
    }
}
