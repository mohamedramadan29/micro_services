<?php

namespace App\Models\front;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderDetail extends Model
{
    use HasFactory;
    protected $guarded = [];

    /////// GEt The User Datta
    /// Seller
    public function seller ()
    {
        return $this->belongsTo(User::class,'user_seller');
    }

    public function buyer()
    {
        return $this->belongsTo(User::class,'user_buyer');
    }
}
