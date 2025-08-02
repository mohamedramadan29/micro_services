<?php

namespace App\Models\admin;

use App\Models\User;
use ReflectionFunctionAbstract;
use Illuminate\Database\Eloquent\Model;

class Blog extends Model
{
    protected $guarded = [];

    public function Category(){
        return $this->belongsTo(BlogCategory::class,'category_id');
    }
    public function Author(){
        return $this->belongsTo(User::class,'author');
    }
    public function Image(){
        return asset('assets/uploads/Blogs/'.$this->image);
    }
}
