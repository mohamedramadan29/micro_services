<?php

namespace App\Models\admin;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory;
    protected $guarded = [];

    // get the category name
    public function category()
    {
        return $this->belongsTo(Category::class,'cat_id');
    }
}
