<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Http\Traits\Message_Trait;
use App\Http\Traits\Upload_Images;
use App\Models\admin\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;

class SettingController extends Controller
{
    use Message_Trait;
    use Upload_Images;

    public function index()
    {
        $setting = Setting::first();
        return view('admin/website_settings.index', compact('setting'));
    }

    public function update(Request $request)
    {
        if ($request->isMethod('post')) {
            $data = $request->all();
            $setting = Setting::first();
            try {
                $rules = [];
                $messages = [];
                $validator = Validator::make($data, $rules, $messages);
                if ($validator->fails()) {
                    return Redirect::back()->withInput()->withErrors($validator);
                }
                if ($request->hasFile('logo')) {
                    if(isset($setting['website_logo']) && $setting['website_logo'] !='' &&$setting['website_logo'] !=null){
                        $old_logo = public_path('assets/uploads/public_setting/' . $setting['website_logo']);
                        if (isset($old_logo) && $old_logo != '') {
                            unlink($old_logo);
                        }
                    }
                    $filename = $this->saveImage($request->logo, public_path('assets/uploads/public_setting'));
                    $setting->update([
                        'website_logo' => $filename,
                    ]);
                }

                $setting->update([
                    'website_title' => $data['website_title'],
                    'website_desc' => $data['website_desc'],
                    'website_color' => $data['website_color'],
                    'website_commission' => $data['website_commission'],
                ]);

                return $this->success_message(' تم تعديل الاعدادات بنجاح  ');
            } catch (\Exception $e) {
                return $this->exception_message($e);
            }
        }
    }
}
