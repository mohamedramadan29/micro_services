<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\NewCourse;
use App\Models\NewCourseLesson;
use App\Models\NewCourseTopic;
use Illuminate\Http\Request;

class NewCourseLessonController extends Controller
{
    public function index(NewCourse $course, NewCourseTopic $topic)
    {
        $lessons = $topic->lessons()->orderBy('sort_order')->get();
        return view('admin.new-courses.lessons.index', compact('course', 'topic', 'lessons'));
    }

    public function create(NewCourse $course, NewCourseTopic $topic)
    {
        return view('admin.new-courses.lessons.create', compact('course', 'topic'));
    }

    public function store(Request $request, NewCourse $course, NewCourseTopic $topic)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'video_url' => 'required|url',
            'duration_minutes' => 'nullable|integer|min:1',
            'is_free' => 'boolean',
            'sort_order' => 'integer|min:0',
        ]);

        $data = $request->all();
        $data['is_free'] = $request->boolean('is_free');

        // Extract YouTube video ID
        if ($data['video_url']) {
            $data['video_id'] = NewCourseLesson::extractYouTubeVideoId($data['video_url']);
        }

        $lesson = $topic->lessons()->create($data);

        return redirect()->route('admin.new-courses.lessons.index', [$course, $topic])
            ->with('success', 'تم إضافة الدرس بنجاح');
    }

    public function show(NewCourse $course, NewCourseTopic $topic, NewCourseLesson $lesson)
    {
        return view('admin.new-courses.lessons.show', compact('course', 'topic', 'lesson'));
    }

    public function edit(NewCourse $course, NewCourseTopic $topic, NewCourseLesson $lesson)
    {
        return view('admin.new-courses.lessons.edit', compact('course', 'topic', 'lesson'));
    }

    public function update(Request $request, NewCourse $course, NewCourseTopic $topic, NewCourseLesson $lesson)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'video_url' => 'required|url',
            'duration_minutes' => 'nullable|integer|min:1',
            'is_free' => 'boolean',
            'sort_order' => 'integer|min:0',
        ]);

        $data = $request->all();
        $data['is_free'] = $request->boolean('is_free');

        // Extract YouTube video ID
        if ($data['video_url']) {
            $data['video_id'] = NewCourseLesson::extractYouTubeVideoId($data['video_url']);
        }

        $lesson->update($data);

        return redirect()->route('admin.new-courses.lessons.index', [$course, $topic])
            ->with('success', 'تم تحديث الدرس بنجاح');
    }

    public function destroy(NewCourse $course, NewCourseTopic $topic, NewCourseLesson $lesson)
    {
        $lesson->delete();

        return redirect()->route('admin.new-courses.lessons.index', [$course, $topic])
            ->with('success', 'تم حذف الدرس بنجاح');
    }

    public function toggleFree(NewCourse $course, NewCourseTopic $topic, NewCourseLesson $lesson)
    {
        $lesson->update(['is_free' => !$lesson->is_free]);
        
        return response()->json([
            'success' => true,
            'message' => $lesson->is_free ? 'تم جعل الدرس مجانياً' : 'تم جعل الدرس مدفوعاً'
        ]);
    }
}
