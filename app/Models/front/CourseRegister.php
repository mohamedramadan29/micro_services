<?php

namespace App\Models\front;

use App\Models\User;
use App\Models\front\Course;
use Illuminate\Database\Eloquent\Model;

class CourseRegister extends Model
{
    protected $guarded = [];

    public function user(){
        return $this->belongsTo(User::class,'user_id');
    }
    public function course(){
        return $this->belongsTo(Course::class,'course_id');
    }
}
