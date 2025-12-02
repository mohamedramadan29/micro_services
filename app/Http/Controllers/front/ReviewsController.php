<?php

namespace App\Http\Controllers\front;

use App\Http\Controllers\Controller;
use App\Http\Traits\Message_Trait;
use App\Models\front\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;

class ReviewsController extends Controller
{
    use Message_Trait;

    public function index()
    {
        return view('website.reviews');
    }

    public function store(Request $request)
    {
        $data = $request->all();
        //dd($data);
        $rules = [
            'name' => 'required|string',
            'rating' => 'required|numeric',
            'notes' => 'required'
        ];
        $messages = [
            'name.required' => ' من فضلك ادخل الاسم الخاص بك  ',
            'rating.required' => ' من فضلك ادخل تقييمك  ',
            'notes.required' => ' من فضلك ادخل ملاحظاتك  ',
        ];
        $validator = Validator::make($data, $rules, $messages);
        if ($validator->fails()) {
            return Redirect()->back()->withInput()->withErrors($validator);
        }
        $review = new Review();
        $review->name = $data['name'];
        $review->reviews_count = $data['rating'];
        $review->more_notes = $data['notes'];
        $review->save();
        return $this->success_message(' شكرا لك علي تقيمك لنا ');
    }
}
