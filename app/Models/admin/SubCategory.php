<?php

namespace App\Models\admin;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubCategory extends Model
{
    protected $guarded = [];
    use HasFactory;

    public function category()
    {
        return $this->belongsTo(Category::class,'parent_id');
    }

    public function Services()
    {
        return $this->hasMany(Service::class,'sub_cat_id');
    }

    public function CountServices()
    {
        return $this->Services()->count();
    }


}
