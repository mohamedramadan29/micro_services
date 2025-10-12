<?php

namespace App\Models\admin;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class PackageSubscribe extends Model
{
    protected $fillable = ['user_id', 'package_id', 'price', 'user_name', 'package_name'];
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function package()
    {
        return $this->belongsTo(Package::class, 'package_id');
    }
}
