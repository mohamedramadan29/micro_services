<?php

namespace App\Http\Controllers\front;

use Illuminate\Http\Request;
use App\Http\Traits\Slug_Trait;
use App\Http\Traits\Message_Trait;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\front\properityMaintain;
use Illuminate\Support\Facades\Validator;

class ProperityMaintainController extends Controller
{
    use Message_Trait;
    use Slug_Trait;
    public function index()
    {
        $properity_maintaines = properityMaintain::with('User')->where('user_id', Auth::user()->id)->get();
        return view('website.properities-maintain.index', compact('properity_maintaines'));
    }

    ############ Start Store Properity ############
    public function store(Request $request)
    {
        if ($request->isMethod('post')) {
            $data = $request->all();
            // dd($data);
            $rules = [
                'title' => 'required',
                'description' => 'required',
                'category' => 'required',
                'contract_type' => 'required',
                'service_type' => 'required',
                'area' => 'required',
                'location' => 'required',
                'city' => 'required',
                'country' => 'required',
            ];
            $messages = [
                'title.required' => 'من فضلك ادخل العنوان',
                'description.required' => 'من فضلك ادخل الوصف',
                'category.required' => 'من فضلك ادخل نوع العقار',
                'contract_type.required' => 'من فضلك ادخل نوع العقد',
                'service_type.required' => 'من فضلك ادخل نوع الخدمة',
                'area.required' => 'من فضلك ادخل المساحة',
                'location.required' => 'من فضلك ادخل الموقع',
                'city.required' => 'من فضلك ادخل المدينة',
                'country.required' => 'من فضلك ادخل الدولة',
            ];
            $validator = Validator::make($data, $rules, $messages);
            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator)->withInput();
            }
            DB::beginTransaction();
            $properity = new properityMaintain();
            $properity->user_id = Auth::user()->id;
            $properity->title = $data['title'];
            $properity->slug = $this->CustomeSlug($data['title']);
            $properity->description = $data['description'];
            $properity->category = $data['category'];
            $properity->contract_type = $data['contract_type'];
            $properity->service_type = $data['service_type'];
            $properity->area = $data['area'] ?: 0;
            $properity->location = $data['location'];
            $properity->city = $data['city'];
            $properity->country = $data['country'];
            $properity->save();
            DB::commit();
            return $this->success_message(' تم اضافة الخدمة بنجاح ');
        }
        return view('website.properities-maintain.create');
    }
    ########### End Store Properity ############

    ############ Start Update Properity ############
    public function update(Request $request, $id)
    {
        $properity_maintaine = properityMaintain::find($id);
        if ($request->isMethod('post')) {
            $data = $request->all();
            // dd($data);
            $rules = [
                'title' => 'required',
                'description' => 'required',
                'category' => 'required',
                'contract_type' => 'required',
                'service_type' => 'required',
                'area' => 'required',
                'location' => 'required',
                'city' => 'required',
                'country' => 'required',
            ];
            $messages = [
                'title.required' => 'من فضلك ادخل العنوان',
                'description.required' => 'من فضلك ادخل الوصف',
                'category.required' => 'من فضلك ادخل نوع العقار',
                'contract_type.required' => 'من فضلك ادخل نوع العقد',
                'service_type.required' => 'من فضلك ادخل نوع الخدمة',
                'area.required' => 'من فضلك ادخل المساحة',
                'location.required' => 'من فضلك ادخل الموقع',
                'city.required' => 'من فضلك ادخل المدينة',
                'country.required' => 'من فضلك ادخل الدولة',
            ];
            $validator = Validator::make($data, $rules, $messages);
            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator)->withInput();
            }
            DB::beginTransaction();

            $properity_maintaine->title = $data['title'];
            $properity_maintaine->slug = $this->CustomeSlug($data['title']);
            $properity_maintaine->description = $data['description'];
            $properity_maintaine->category = $data['category'];
            $properity_maintaine->contract_type = $data['contract_type'];
            $properity_maintaine->service_type = $data['service_type'];
            $properity_maintaine->area = $data['area'] ?: 0;
            $properity_maintaine->location = $data['location'];
            $properity_maintaine->city = $data['city'];
            $properity_maintaine->country = $data['country'];
            $properity_maintaine->save();
            DB::commit();
            return $this->success_message(' تم تحديث الخدمة بنجاح ');
        }
        return view('website.properities-maintain.edit', compact('properity_maintaine'));
    }
    ########### End Update Properity ############

    ############ Start Delete Properity ############
    public function delete(Request $request, $id)
    {
        $property = properityMaintain::find($id);
        $property->delete();
        return $this->success_message('تم حذف الخدمة بنجاح');
    }
    ########### End Delete Properity ############
}
