<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\front\Review;
use Illuminate\Http\Request;

class ReviewsController extends Controller
{
    public function index()
    {
        $reviews = Review::latest()->get();
        return view('admin.reviews.index', compact('reviews'));
    }

    public function details($id)
    {
        $review = Review::findOrFail($id);
        return view('admin.reviews.details', compact('review'));
    }
}
