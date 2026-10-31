<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Suggestion types (ميزة جديدة، تحسين ميزة…), managed in the suggestions settings. */
class SuggestionCategory extends Model
{
    protected $guarded = [];

    protected $casts = ['is_active' => 'boolean'];

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('sort_order')->orderBy('id');
    }

    public function suggestions()
    {
        return $this->hasMany(Suggestion::class, 'category_id');
    }
}
