<?php

namespace App\Models\admin;

use Illuminate\Database\Eloquent\Model;

class PublicCourseRegister extends Model
{
    protected $guarded = [];

    public function course()
    {
        return $this->belongsTo(PublicCourse::class, 'public_course_id');
    }
}
