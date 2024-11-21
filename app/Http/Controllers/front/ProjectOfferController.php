<?php

namespace App\Http\Controllers\front;

use App\Http\Requests\StoreProjectOfferRequest;
use App\Http\Requests\UpdateProjectOfferRequest;
use App\Http\Traits\Message_Trait;
use App\Http\Traits\Upload_Images;
use App\Models\front\ProjectOffer;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\admin\Setting;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ProjectOfferController extends Controller
{
    use Message_Trait;
    use Upload_Images;
    public function store(Request $request){
        $public_setting  = Setting::first();
       // dd($public_setting);
        $website_commition = floatval($public_setting['website_commission']); // 10%
      //  dd($website_commition);
        try{
            $data = $request->all();
            $website_get_commision = $data['offer_value'] * $website_commition/100;
            $user_get_commision = $data['offer_value'] - $website_get_commision;
            $rules = [
                "execution_time"=> "required|numeric|max:90",
                'offer_value'=>'required|numeric|max:5000',
                'offer_details'=>'required|min:20'
            ];
            $messages = [
                'execution_time.required'=> ' من فضلك حدد وقت التنفيذ ',
                'execution_time.numeric'=>' من فضلك حدد وقت التنفيذ بشكل صحيح  ',
                'execution_time.max'=>' من فضلك حدد وقت التنفيذ اقل من 90 يوم   ',
                'offer_value.required'=> ' من فضلك ادخل عرضك ',
                'offer_value.numeric'=> ' من فضلك ادخل العرض رقمي صحيح  ',
                'offer_value.max'=> ' من فضلك ادخل العرض اقل من 5000 دولار   ',
                'offer_details.required'=> ' من فضلك ادخل العرض  ',
                'offer_details.min'=> ' من فضلك ادخل  عرض كافي ووافي اقل عدد احرف 50  '
            ];
            $validator = Validator::make($data, $rules, $messages);
            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator)->withInput();
            }

            $offer = new projectOffer();
            $offer->project_id = $data['project_id'];
            $offer->user_id = Auth::user()->id;
            $offer->day_number = $data['execution_time'];
            $offer->offer_price = $data['offer_value'];
            $offer->proposal = $data['offer_details'];
            $offer->user_get = $user_get_commision;
            $offer->website_get = $website_get_commision;
            $offer->save();
            return $this->success_message(' تم اضافة العرض الخاص بك بنجاح  ');

        }catch(\Exception $e){
            return $this->exception_message($e);
        }

    }
}
