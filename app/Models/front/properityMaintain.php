<?php

namespace App\Models\front;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class properityMaintain extends Model
{
    protected $guarded = [];
    public function User()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
