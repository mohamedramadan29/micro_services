<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Support\Str;

class NewCourse extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'image',
        'description',
        'short_description',
        'price',
        'is_free',
        'is_active',
        'sort_order',
        'meta_title',
        'meta_description',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_free' => 'boolean',
        'is_active' => 'boolean',
    ];

    public static function boot()
    {
        parent::boot();

        static::creating(function ($course) {
            if (empty($course->slug)) {
                $course->slug = Str::slug($course->title);
            }
        });

        static::updating(function ($course) {
            if ($course->isDirty('title') && empty($course->slug)) {
                $course->slug = Str::slug($course->title);
            }
        });
    }

    public function topics(): HasMany
    {
        return $this->hasMany(NewCourseTopic::class, 'new_course_id')->orderBy('sort_order');
    }

    public function lessons(): HasManyThrough
    {
        return $this->hasManyThrough(
            NewCourseLesson::class,
            NewCourseTopic::class,
            'new_course_id',
            'new_course_topic_id'
        )->orderBy('sort_order');
    }

    public function getTotalLessonsCountAttribute(): int
    {
        return $this->lessons()->count();
    }

    public function getTotalDurationAttribute(): int
    {
        return $this->lessons()->sum('duration_minutes') ?? 0;
    }

    public function getImageUrlAttribute(): string
    {
        return asset('storage/' . $this->image);
    }
}
