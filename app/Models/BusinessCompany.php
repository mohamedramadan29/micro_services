<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** «نفّذها للأعمال»: the company profile of a client account (one user per company for now). */
class BusinessCompany extends Model
{
    public const SIZES = ['small' => 'شركة صغيرة', 'medium' => 'شركة متوسطة', 'large' => 'شركة كبيرة'];

    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function sizeLabel(): string
    {
        return self::SIZES[$this->size] ?? '—';
    }
}
