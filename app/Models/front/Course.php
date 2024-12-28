<?php

namespace App\Models\front;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\User;
class Course extends Model
{
    use HasFactory;
    protected $guarded = [];
    public function User()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function Subscriptions()
    {
        return $this->hasMany(CourseRegister::class, 'course_id');
    }

}
