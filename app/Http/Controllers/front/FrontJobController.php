<?php

namespace App\Http\Controllers\front;

use App\Http\Traits\Upload_Images;
use App\Models\front\Jobs;
use Illuminate\Http\Request;
use App\Models\front\JobOffer;
use App\Http\Traits\Message_Trait;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class FrontJobController extends Controller
{
    use Message_Trait;
    use Upload_Images;
    public function index()
    {
        $jobs = Jobs::where('status', 1)->orderBy('created_at', 'desc')->paginate(10);
        return view('website.jobs', compact('jobs'));
    }

    public function jobDetails($id, $slug)
    {
        $job = Jobs::with('offers')->where('id', $id)->where('slug', $slug)->first();
     //   $userOfferCheck =
      //  dd($job);
        if (!$job) {
            abort(404);
        }
        return view('website.job-details', compact('job'));
    }

    public function OfferStore(Request $request, $id)
    {
        if (!auth()->check()) {
            return redirect()->route('login')->with('error', __('public.please_login_first'));
        }

        if ($id != $request->job_id) {
            abort(404);
        }
        $data = $request->all();
        $rules = [
            'job_id' => "required|exists:jobs,id",
            'offer_description' => 'required|string|max:500',
            'offer_file' => 'nullable|mimes:pdf,doc,docx|max:10240'

        ];
        $messages = [
            'job_id.required' => 'رقم الوظيفة مطلوب',
            'job_id.exists' => 'رقم الوظيفة غير صحيح',
            'offer_description.required' => 'وصف العرض مطلوب',
            'offer_description.string' => 'وصف العرض يجب أن يكون نص',
            'offer_description.max' => 'وصف العرض يجب ألا يتجاوز 500 حرف',
            'offer_file.mimes' => 'ملف العرض يجب أن يكون بصيغة pdf أو doc أو docx',
            'offer_file.max' => 'حجم الملف يجب ألا يتجاوز 10 ميجابايت'
        ];
        $validator = Validator::make($data, $rules, $messages);
        if ($validator->fails()) {
            return Redirect()->back()->withErrors($validator)->withInput();
        }


        $offer = new JobOffer();
        $offer->job_id = $request->job_id;
        $offer->user_id = Auth::user()->id;
        $offer->offer_description = $data["offer_description"];
        $offer->status = 'pending';

        if ($request->hasFile('offer_file')) {
            $fileName = $this->saveImage($request->file('offer_file'), public_path('assets/uploads/jobOffers'));
            $offer->offer_file = $fileName;
        }
        $offer->save();
        return $this->success_message(' تم اضافة العرض الخاص بك بنجاح  ');
    }
}
