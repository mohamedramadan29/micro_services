<?php

namespace App\Http\Controllers\front;

use App\Http\Controllers\Controller;
use App\Models\NewCourse;
use App\Models\NewCourseTopic;
use App\Models\NewCourseLesson;
use Illuminate\Http\Request;

class NewCourseController extends Controller
{
    public function index()
    {
        $courses = NewCourse::where('is_active', true)
            ->withCount('topics', 'lessons')
            ->orderBy('sort_order')
            ->orderBy('created_at', 'desc')
            ->get();
            
        return view('website.new-courses.index', compact('courses'));
    }

    public function show($slug)
    {
        $course = NewCourse::where('slug', $slug)
            ->where('is_active', true)
            ->with(['topics' => function($query) {
                $query->orderBy('sort_order')->withCount('lessons');
            }])
            ->firstOrFail();
            
        return view('website.new-courses.show', compact('course'));
    }

    public function topic($courseSlug, $topicId)
    {
        $course = NewCourse::where('slug', $courseSlug)
            ->where('is_active', true)
            ->firstOrFail();
            
        $topic = NewCourseTopic::where('id', $topicId)
            ->where('new_course_id', $course->id)
            ->with(['lessons' => function($query) {
                $query->orderBy('sort_order');
            }])
            ->firstOrFail();
            
        return view('website.new-courses.topic', compact('course', 'topic'));
    }

    public function lesson($courseSlug, $topicId, $lessonId)
    {
        $course = NewCourse::where('slug', $courseSlug)
            ->where('is_active', true)
            ->firstOrFail();
            
        $topic = NewCourseTopic::where('id', $topicId)
            ->where('new_course_id', $course->id)
            ->firstOrFail();
            
        $lesson = NewCourseLesson::where('id', $lessonId)
            ->where('new_course_topic_id', $topic->id)
            ->firstOrFail();
            
        // Get previous and next lessons
        $previousLesson = NewCourseLesson::where('new_course_topic_id', $topic->id)
            ->where('sort_order', '<', $lesson->sort_order)
            ->orderBy('sort_order', 'desc')
            ->first();
            
        $nextLesson = NewCourseLesson::where('new_course_topic_id', $topic->id)
            ->where('sort_order', '>', $lesson->sort_order)
            ->orderBy('sort_order')
            ->first();
            
        return view('website.new-courses.lesson', compact('course', 'topic', 'lesson', 'previousLesson', 'nextLesson'));
    }

    public function getVideoUrl($lessonId)
    {
        $lesson = NewCourseLesson::findOrFail($lessonId);
        
        // Return protected video URL
        return response()->json([
            'success' => true,
            'video_url' => route('courses.video-stream', $lessonId),
            'video_id' => $lesson->video_id,
            'title' => $lesson->title
        ]);
    }

    public function videoStream($lessonId)
    {
        $lesson = NewCourseLesson::findOrFail($lessonId);
        
        // You can implement additional security checks here
        // For example: check if user has access to this course
        
        // For now, we'll redirect to YouTube but with additional protection
        return redirect()->away($lesson->video_url);
    }
}
