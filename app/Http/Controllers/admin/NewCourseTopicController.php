<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\NewCourse;
use App\Models\NewCourseTopic;
use Illuminate\Http\Request;

class NewCourseTopicController extends Controller
{
    public function index(NewCourse $course)
    {
        $topics = $course->topics()->withCount('lessons')->orderBy('sort_order')->get();
        return view('admin.new-courses.topics.index', compact('course', 'topics'));
    }

    public function create(NewCourse $course)
    {
        return view('admin.new-courses.topics.create', compact('course'));
    }

    public function store(Request $request, NewCourse $course)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'sort_order' => 'integer|min:0',
        ]);

        $topic = $course->topics()->create($request->all());

        return redirect()->route('admin.new-courses.topics.index', $course)
            ->with('success', 'تم إضافة الموضوع بنجاح');
    }

    public function show(NewCourse $course, NewCourseTopic $topic)
    {
        $topic->load('lessons');
        return view('admin.new-courses.topics.show', compact('course', 'topic'));
    }

    public function edit(NewCourse $course, NewCourseTopic $topic)
    {
        return view('admin.new-courses.topics.edit', compact('course', 'topic'));
    }

    public function update(Request $request, NewCourse $course, NewCourseTopic $topic)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'sort_order' => 'integer|min:0',
        ]);

        $topic->update($request->all());

        return redirect()->route('admin.new-courses.topics.index', $course)
            ->with('success', 'تم تحديث الموضوع بنجاح');
    }

    public function destroy(NewCourse $course, NewCourseTopic $topic)
    {
        $topic->delete();

        return redirect()->route('admin.new-courses.topics.index', $course)
            ->with('success', 'تم حذف الموضوع بنجاح');
    }
}
