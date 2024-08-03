<?php

namespace App\Models\front;

use App\Models\admin\Service;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class Cart extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function serviceData()
    {
        return $this->belongsTo(Service::class,'service_id');
    }
    public function UserData()
    {
        return $this->belongsTo(User::class,'user_serv')->select('name','id','user_name');
    }
    public static function getCartItems()
    {
        if (Auth::check()) {
            // if User Logged In // Pick The User Id
            $user_id = Auth::user()->id;
            $getcartItems = Cart::with('serviceData','userData')->where('user_id', $user_id)->get();
        } else {
            // If User Not Login // Pick The Session ID
            $session_id = Session::get('session_id');
            $getcartItems = Cart::with('serviceData','userData')->where('session_id', $session_id)->get();
        }

        return $getcartItems;
    }

    public static function clear()
    {
        Cart::where('user_id',Auth::id())->delete();
    }
}
