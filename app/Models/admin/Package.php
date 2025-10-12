<?php

namespace App\Models\admin;

use Illuminate\Database\Eloquent\Model;

class Package extends Model
{
    protected $guarded = [];
    public function subscribes()
    {
        return $this->hasMany(PackageSubscribe::class, 'package_id');
    }
}
