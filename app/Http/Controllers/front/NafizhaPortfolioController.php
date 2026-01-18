<?php

namespace App\Http\Controllers\front;

use App\Http\Controllers\Controller;
use App\Models\admin\nafizhaPortfolio;
use Illuminate\Http\Request;

class NafizhaPortfolioController extends Controller
{
    public function index()
    {
        $works = nafizhaPortfolio::latest()->paginate(12);
        return view('website.portfolio-nafizha.index', compact('works'));
    }

    public function details($id, $slug)
    {
        $work = nafizhaPortfolio::with(['Category', 'User'])->findOrFail($id);
        return view('website.portfolio-nafizha.details', compact('work'));
    }
}
