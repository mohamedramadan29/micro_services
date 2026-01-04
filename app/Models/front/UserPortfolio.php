<?php

namespace App\Models\front;

use App\Models\admin\SubCategory;
use App\Models\User;
use App\Models\admin\Category;
use Illuminate\Database\Eloquent\Model;

class UserPortfolio extends Model
{

    protected $table = 'user_portfolios';
    protected $fillable = [
        'id',
        'user_id',
        'title',
        'slug',
        'description',
        'link',
        'image',
        'more_images',
        'tools',
        'category_id',
        'status'
    ];

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
