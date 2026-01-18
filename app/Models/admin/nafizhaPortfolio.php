<?php

namespace App\Models\admin;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class nafizhaPortfolio extends Model
{
    protected $guarded = [];

     public function Category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function User()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function getSubCategoriesAttribute()
    {
        if (!$this->tools) return collect();

        $ids = array_filter(explode(',', $this->tools));

        return SubCategory::whereIn('id', $ids)->get();
    }

    protected $casts = [
        'more_images' => 'array',
    ];
}
