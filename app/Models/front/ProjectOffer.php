<?php

namespace App\Models\front;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectOffer extends Model
{
    use HasFactory;

    protected $fillable = ['user_id','project_id','day_number','offer_price','user_get','website_get','proposal','file','status'];

    public function User(){
        return $this->belongsTo(User::class,'user_id');
    }
}
