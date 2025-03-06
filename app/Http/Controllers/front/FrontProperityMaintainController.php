<?php

namespace App\Http\Controllers\front;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\front\ProperityMaintain;
class FrontProperityMaintainController extends Controller
{
    public function index()
    {
        $properity_maintains = properityMaintain::where('active', 1)->orderBy('id', 'desc')->paginate(10);
        return view('website.properity-maintain', compact('properity_maintains'));
    }
    public function propertyDetails($id, $slug)
    {
        $properity_maintain = properityMaintain::where('id', $id)->where('slug', $slug)->first();
        if (!$properity_maintain) {
            abort(404, 'خدمة صيانة غير موجودة');
        }
        return view('website.properity-maintain-details', compact('properity_maintain'));
    }
}
