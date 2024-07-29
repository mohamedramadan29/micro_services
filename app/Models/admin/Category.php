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

//    public function subCategories()
//    {
//        return $this->belongsTo(SubCategory::class,'parent_id');
//    }
    // Get the subcategories
    public function subCategories()
    {
        return $this->hasMany(SubCategory::class, 'parent_id');
    }

    // Get the count of subcategories
    public function countSubCategories()
    {
        return $this->subCategories()->count();
    }


//    public function countsubcategories($parent_category)
//    {
//        $countSubCategories = SubCategory::where('parent_id',$parent_category)->count();
//        return $countSubCategories;
//    }


}
