<?php

namespace App\Models\admin;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $guarded = [];

    /// get the category parents
    public function parents()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }
    // Get the Services Number
    public function services()
    {
        return $this->hasMany(Service::class,'cat_id');
    }



}
