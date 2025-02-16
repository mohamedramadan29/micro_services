<?php

namespace App\Http\Controllers\front;

use Illuminate\Http\Request;
use App\Models\front\Properity;
use App\Http\Controllers\Controller;

class FrontProperityController extends Controller
{
    public function index(){
        $properities = Properity::with('ProperityFirstImage','ProperityImages')->orderBy('created_at', 'desc')->paginate(10);
        return view('website.properities', compact('properities'));
    }

    public function propertyDetails($id,$slug){

        $property = Properity::with('ProperityFirstImage','ProperityImages')->where('id', $id)->where('slug', $slug)->first();

        return view('website.property-details', compact('property'));
    }
}
