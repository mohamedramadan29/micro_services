<?php

namespace App\Models\admin;

use Illuminate\Database\Eloquent\Model;

class BlogCategory extends Model
{
    protected $guarded = [];

    public function Image(){
        return asset('assets/uploads/BlogCategory/'.$this->image);
    }

    public function ScopeActive($query){
        return $query->whereStatus(1);
    }

    public function blogs(){
        return $this->hasMany(Blog::class,'category_id');
    }
}
