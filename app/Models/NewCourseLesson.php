<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NewCourseLesson extends Model
{
    use HasFactory;

    protected $fillable = [
        'new_course_topic_id',
        'title',
        'description',
        'video_url',
        'video_id',
        'duration_minutes',
        'is_free',
        'sort_order',
    ];

    protected $casts = [
        'is_free' => 'boolean',
        'duration_minutes' => 'integer',
    ];

    public static function boot()
    {
        parent::boot();

        static::saving(function ($lesson) {
            if ($lesson->video_url && !$lesson->video_id) {
                $lesson->video_id = self::extractYouTubeVideoId($lesson->video_url);
            }
        });
    }

    public function topic(): BelongsTo
    {
        return $this->belongsTo(NewCourseTopic::class, 'new_course_topic_id');
    }

    public function getCourse(): NewCourse
    {
        return $this->topic->course;
    }

    public function getVideoEmbedUrlAttribute(): string
    {
        if ($this->video_id) {
            return "https://www.youtube.com/embed/{$this->video_id}";
        }
        return '';
    }

    public function getThumbnailUrlAttribute(): string
    {
        if ($this->video_id) {
            return "https://img.youtube.com/vi/{$this->video_id}/hqdefault.jpg";
        }
        return '';
    }

    public static function extractYouTubeVideoId(string $url): ?string
    {
        $patterns = [
            '/youtube\.com\/watch\?v=([a-zA-Z0-9_-]+)/',
            '/youtube\.com\/embed\/([a-zA-Z0-9_-]+)/',
            '/youtu\.be\/([a-zA-Z0-9_-]+)/',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $url, $matches)) {
                return $matches[1];
            }
        }

        return null;
    }

    public function getFormattedDurationAttribute(): string
    {
        if (!$this->duration_minutes) {
            return 'غير محدد';
        }

        $hours = floor($this->duration_minutes / 60);
        $minutes = $this->duration_minutes % 60;

        if ($hours > 0) {
            return $hours . ' ساعة ' . ($minutes > 0 ? $minutes . ' دقيقة' : '');
        }

        return $minutes . ' دقيقة';
    }
}
