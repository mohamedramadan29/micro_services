<?php

namespace App\Models\admin;

use Illuminate\Database\Eloquent\Model;

class PublicCourse extends Model
{
    protected $guarded = [];

    public function Image()
    {
        return asset('assets/uploads/public-courses/' . $this->image);
    }
    public function registers()
    {
        return $this->hasMany(PublicCourseRegister::class, 'public_course_id')->orderBy('id', 'desc');
    }
}
