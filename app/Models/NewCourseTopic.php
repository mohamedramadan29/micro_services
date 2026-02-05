<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NewCourseTopic extends Model
{
    use HasFactory;

    protected $fillable = [
        'new_course_id',
        'title',
        'description',
        'sort_order',
    ];

    public function course(): BelongsTo
    {
        return $this->belongsTo(NewCourse::class, 'new_course_id');
    }

    public function lessons(): HasMany
    {
        return $this->hasMany(NewCourseLesson::class, 'new_course_topic_id')->orderBy('sort_order');
    }

    public function getLessonsCountAttribute(): int
    {
        return $this->lessons()->count();
    }

    public function getTotalDurationAttribute(): int
    {
        return $this->lessons()->sum('duration_minutes') ?? 0;
    }
}
