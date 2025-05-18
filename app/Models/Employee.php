<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;

    protected $table = 'users';

    public function scopeEmployees($query)
    {
        return $query->where('account_type', 'موظف');
    }

    public function getFullNameAttribute()
    {
        return $this->name;
    }

    public function getProfileImageAttribute()
    {
        return $this->image ? asset('assets/uploads/users/' . $this->image) : asset('assets/website/img/default-avatar.png');
    }
}
