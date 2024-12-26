<?php

namespace App\Models\admin;

use Illuminate\Database\Eloquent\Model;

class Consultant extends Model
{
    protected $gaureded = [];

    public function category()
    {
        return $this->belongsTo(Category::class,'specialization');
    }
}
