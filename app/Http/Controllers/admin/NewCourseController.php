<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Http\Traits\Upload_Images;
use App\Models\NewCourse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class NewCourseController extends Controller
{
    use Upload_Images;
    public function index()
    {
        $courses = NewCourse::withCount('topics')
            ->withCount('lessons')
            ->orderBy('sort_order')
            ->get();

        return view('admin.new-courses.index', compact('courses'));
    }

    public function create()
    {
        return view('admin.new-courses.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'short_description' => 'nullable|string|max:500',
            'price' => 'required|numeric|min:0',
            'is_free' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'integer|min:0',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
        ]);

        $data = $request->except('image');

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $filename = $this->saveImage($image, public_path('assets/uploads/free-courses'));
            $data['image'] = 'assets/uploads/free-courses/' . $filename;
        }

        $data['slug'] = Str::slug($request->title);
        $data['is_free'] = $request->boolean('is_free');
        $data['is_active'] = $request->boolean('is_active');

        NewCourse::create($data);

        return redirect()->route('admin.new-courses.index')
            ->with('success', 'تم إضافة الكورس بنجاح');
    }

    public function show(NewCourse $newCourse)
    {
        $newCourse->load(['topics.lessons']);
        return view('admin.new-courses.show', compact('newCourse'));
    }

    public function edit(NewCourse $newCourse)
    {
        return view('admin.new-courses.edit', compact('newCourse'));
    }

    public function update(Request $request, NewCourse $newCourse)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'short_description' => 'nullable|string|max:500',
            'price' => 'required|numeric|min:0',
            'is_free' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'integer|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
        ]);

        $data = $request->except('image');

        if ($request->hasFile('image')) {
            // Delete old image
            if ($newCourse->image) {
                $oldImagePath = public_path($newCourse->image);
                if (file_exists($oldImagePath)) {
                    unlink($oldImagePath);
                }
            }

            $image = $request->file('image');
            $filename = $this->saveImage($image, public_path('assets/uploads/free-courses'));
            $data['image'] = 'assets/uploads/free-courses/' . $filename;
        }

        $data['is_free'] = $request->boolean('is_free');
        $data['is_active'] = $request->boolean('is_active');

        $newCourse->update($data);

        return redirect()->route('admin.new-courses.index')
            ->with('success', 'تم تحديث الكورس بنجاح');
    }

    public function destroy(NewCourse $newCourse)
    {
        // Delete image
        if ($newCourse->image) {
            $imagePath = public_path($newCourse->image);
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }
        }

        $newCourse->delete();

        return redirect()->route('admin.new-courses.index')
            ->with('success', 'تم حذف الكورس بنجاح');
    }

    public function toggleStatus(NewCourse $newCourse)
    {
        $newCourse->update(['is_active' => !$newCourse->is_active]);

        return response()->json([
            'success' => true,
            'message' => $newCourse->is_active ? 'تم تفعيل الكورس' : 'تم إلغاء تفعيل الكورس'
        ]);
    }
}
