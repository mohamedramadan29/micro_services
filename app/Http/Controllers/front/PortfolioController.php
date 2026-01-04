<?php

namespace App\Http\Controllers\front;

use App\Http\Controllers\Controller;
use App\Models\front\UserPortfolio;
use Illuminate\Http\Request;

class PortfolioController extends Controller
{
    public function index()
    {
        $works = UserPortfolio::latest()->paginate(12);
        return view('website.portfolio.index', compact('works'));
    }

    public function details($id, $slug)
    {
        $work = UserPortfolio::with(['Category', 'User'])->findOrFail($id);
        return view('website.portfolio.details', compact('work'));
    }
}
