<?php

namespace App\Models\front;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class WithDraw extends Model
{
    protected $guarded = [];

    public function user(){
        return $this->belongsTo(User::class,'user_id');
    }
}
