<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\front\Review;
use Illuminate\Http\Request;
use App\Http\Traits\Upload_Images;

class ReviewController extends Controller
{
    use Upload_Images;

    public function index()
    {
        $reviews = Review::orderBy('sort_order')->orderBy('created_at', 'desc')->get();
        return view('admin.reviews.index', compact('reviews'));
    }

    public function create()
    {
        return view('admin.reviews.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'client_name' => 'required|string|max:255',
            'client_position' => 'nullable|string|max:255',
            'client_company' => 'nullable|string|max:255',
            'client_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'content' => 'required|string',
            'rating' => 'required|integer|min:1|max:5',
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
            'is_approved' => 'boolean',
            'sort_order' => 'integer|min:0',
        ]);

        $data = $request->all();
        $data['is_featured'] = $request->boolean('is_featured');
        $data['is_active'] = $request->boolean('is_active');
        $data['is_approved'] = $request->boolean('is_approved');

        // Handle image upload
        if ($request->hasFile('client_image')) {
            $data['client_image'] = $this->saveImage($request->file('client_image'), public_path('assets/uploads/reviews'));
        }

        Review::create($data);

        return redirect()->route('admin.reviews.index')
            ->with('success', 'تم إضافة رأي العميل بنجاح');
    }

    public function show(Review $review)
    {
        return view('admin.reviews.show', compact('review'));
    }

    public function edit(Review $review)
    {
        return view('admin.reviews.edit', compact('review'));
    }

    public function update(Request $request, Review $review)
    {
        $request->validate([
            'client_name' => 'required|string|max:255',
            'client_position' => 'nullable|string|max:255',
            'client_company' => 'nullable|string|max:255',
            'client_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'content' => 'required|string',
            'rating' => 'required|integer|min:1|max:5',
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
            'is_approved' => 'boolean',
            'sort_order' => 'integer|min:0',
        ]);

        $data = $request->all();
        $data['is_featured'] = $request->boolean('is_featured');
        $data['is_active'] = $request->boolean('is_active');
        $data['is_approved'] = $request->boolean('is_approved');

        // Handle image upload
        if ($request->hasFile('client_image')) {
            // Delete old image
            if ($review->client_image) {
                $oldImagePath = public_path($review->client_image);
                if (file_exists($oldImagePath)) {
                    unlink($oldImagePath);
                }
            }
            $data['client_image'] = $this->saveImage($request->file('client_image'), public_path('assets/uploads/reviews'));
        }

        $review->update($data);

        return redirect()->route('admin.reviews.index')
            ->with('success', 'تم تحديث رأي العميل بنجاح');
    }

    public function destroy(Review $review)
    {
        // Delete image
        if ($review->client_image) {
            $imagePath = public_path($review->client_image);
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }
        }

        $review->delete();

        return redirect()->route('admin.reviews.index')
            ->with('success', 'تم حذف رأي العميل بنجاح');
    }

    public function toggleStatus(Review $review)
    {
        $review->is_active = !$review->is_active;
        $review->save();

        return response()->json(['success' => true, 'status' => $review->is_active]);
    }

    public function toggleApproved(Review $review)
    {
        $review->is_approved = !$review->is_approved;
        $review->save();

        return response()->json(['success' => true, 'approved' => $review->is_approved]);
    }

    public function toggleFeatured(Review $review)
    {
        $review->is_featured = !$review->is_featured;
        $review->save();

        return response()->json(['success' => true, 'featured' => $review->is_featured]);
    }
}
