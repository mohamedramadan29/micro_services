<?php

namespace App\Http\Controllers\front;

use App\Http\Requests\StoreProjectOfferRequest;
use App\Http\Requests\UpdateProjectOfferRequest;
use App\Http\Traits\Message_Trait;
use App\Http\Traits\Upload_Images;
use App\Models\front\Project;
use App\Models\front\ProjectOffer;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\admin\Setting;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;

class ProjectOfferController extends Controller
{
    use Message_Trait;
    use Upload_Images;

    public function store(Request $request)
    {
        $public_setting = Setting::first();
        // dd($public_setting);
        $website_commition = floatval($public_setting['website_commission']); // 10%
        //  dd($website_commition);
        try {
            $data = $request->all();
            $website_get_commision = $data['offer_value'] * $website_commition / 100;
            $user_get_commision = $data['offer_value'] - $website_get_commision;
            $rules = [
                "execution_time" => "required|numeric|max:90",
                'offer_value' => 'required|numeric|max:5000',
                'offer_details' => 'required|min:20'
            ];
            $messages = [
                'execution_time.required' => ' من فضلك حدد وقت التنفيذ ',
                'execution_time.numeric' => ' من فضلك حدد وقت التنفيذ بشكل صحيح  ',
                'execution_time.max' => ' من فضلك حدد وقت التنفيذ اقل من 90 يوم   ',
                'offer_value.required' => ' من فضلك ادخل عرضك ',
                'offer_value.numeric' => ' من فضلك ادخل العرض رقمي صحيح  ',
                'offer_value.max' => ' من فضلك ادخل العرض اقل من 5000 دولار   ',
                'offer_details.required' => ' من فضلك ادخل العرض  ',
                'offer_details.min' => ' من فضلك ادخل  عرض كافي ووافي اقل عدد احرف 50  '
            ];
            $validator = Validator::make($data, $rules, $messages);
            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator)->withInput();
            }

            $project_id = $data['project_id'];
            $offers = ProjectOffer::where('project_id', $project_id)->where('user_id', Auth::user()->id)->get();
            if ($offers->count() > 0) {
                return Redirect::back()->withErrors(' تم اضافة العرض الخاص علي المشروع من قبل  ');
            }

            $project_data = Project::findOrFail($project_id);
            $project_owner = $project_data['user_id'];
            if ($project_owner == Auth::user()->id) {
                return Redirect::back()->withErrors(' لا يمكنك تقديم عرض علي المشروع الخاص بك   ');
            }
            if (in_array($project_data['status'], ['تنفيذ المشروع', 'تم الاستلام'])) {
                return redirect()->back()->withErrors('لا يمكن تقديم عروض أخرى على المشروع!');
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

        } catch (\Exception $e) {
            return $this->exception_message($e);
        }

    }


    public function accept_offer($offer_id)
    {
        $offer = ProjectOffer::findOrFail($offer_id);
        $project_id = $offer['project_id'];
        $project = Project::where('id', $project_id)->first();
        DB::beginTransaction();
        try {
            if ($offer) {
                if ($project) {
                    $offer->update([
                        'status' => 1, //////// يعني تم قبول هذا العرض
                    ]);
                    ///// Update Project Data
                    $project->update([
                        'freelancer_id' => $offer['user_id'],
                        'status' => 'تنفيذ المشروع',
                        'offer_accept' => $offer_id,
                        'offer_days' => $offer['day_number'],
                        'offer_budget' => $offer['offer_price']
                    ]);
                    DB::commit();
                    return $this->success_message(' تمت الموافقة علي العرض بنجاح  ');
                }
                abort(404);
            }
            abort(404);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->exception_message($e);
        }

    }

    public function accept_project($project_id)
    {
        try {
            // جلب بيانات المشروع
            $project = Project::findOrFail($project_id);

            // التأكد من أن المشروع في حالة مناسبة للقبول
            if ($project->status !== 'تنفيذ المشروع') {
                //    return $this->error_message('');
                return Redirect::back()->withErrors('لا يمكن تسليم هذا المشروع في الحالة الحالية.');
            }

            DB::beginTransaction();

            // تحديث حالة المشروع
            $project->update([
                'status' => 'تم الاستلام',
            ]);

            // جلب العرض المقبول
            $offer = ProjectOffer::find($project->offer_accept);
            if (!$offer) {
                DB::rollBack();
                return Redirect::back()->withErrors('العرض المقبول غير موجود.');
            }

            $offer_user_get = $offer->user_get;
            $website_get = $offer->website_get;

            // تحديث رصيد المستخدم المستقل
            $freelancer = User::find($project->freelancer_id);
            if ($freelancer) {
               // $freelancer->increment('balance', $offer_user_get);
                $freelancer->balance = intval($freelancer['balance']) + intval($offer_user_get);
                $freelancer->save();
            } else {
                DB::rollBack();
                return Redirect::back()->withErrors('المستقل غير موجود.');
            }
            // تحديث رصيد صاحب المشروع
            $project_owner = User::find($project->user_id);
            if ($project_owner) {
                $project_owner->balance = intval($project_owner['balance']) - intval($project['offer_budget']);
                $project_owner->save();

            } else {
                DB::rollBack();
                return Redirect::back()->withErrors('صاحب المشروع غير موجود.');
            }
            DB::commit();
            return $this->success_message('تم تسليم المشروع بنجاح.');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->exception_message($e->getMessage());
        }
    }

}
