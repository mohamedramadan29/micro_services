<?php

namespace App\Models\front;

use Illuminate\Database\Eloquent\Model;

class Properity extends Model
{
    protected $guarded = [];

    public function ProperityImages(){
        return $this->hasMany(ProperityImage::class,'properity_id');
    }
    public function ProperityFirstImage(){
        return $this->hasOne(ProperityImage::class,'properity_id')->oldest();
    }
}
