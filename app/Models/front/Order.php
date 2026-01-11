<?php

namespace App\Models\front;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Order extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function Seller()
    {
        return $this->belongsTo(User::class, 'user_seller');
    }

    public function Buyer()
    {
        return $this->belongsTo(User::class, 'user_buyer');
    }

    public function OrderDetail(){
        return $this->belongsTo(OrderDetail::class,'id','order_id');
    }
}
