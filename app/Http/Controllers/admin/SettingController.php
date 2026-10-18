<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Http\Traits\Message_Trait;
use App\Http\Traits\Upload_Images;
use App\Models\admin\Setting;
use App\Services\GmailService;
use App\Services\GoogleSheetsService;
use App\Services\ServiceRequests\NotificationSettings;
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
        if (!$setting) {
            $setting = Setting::create([
                'website_commission' => '10',
                'hero_overlay_color' => '#00000057',
            ]);
        }
        return view('admin/website_settings.index', compact('setting'));
    }

    public function update(Request $request)
    {
        if ($request->isMethod('post')) {
            $data = $request->all();
            $setting = Setting::first();
            try {
                $rules = [
                    'website_commission' => 'required|numeric|min:0|max:100',
                    'repeat_client_threshold' => 'required|integer|min:2|max:100',
                    'stage_fee_percent' => 'nullable|numeric|min:0|max:20',
                    'stage_fee_fixed' => 'nullable|numeric|min:0|max:1000',
                    'client_count_mode' => ['nullable', \Illuminate\Validation\Rule::in(array_keys(\App\Services\ServiceRequests\ClientClassifier::COUNT_MODES))],
                ];
                $messages = [
                    'website_commission.required' => 'من فضلك ادخل نسبة عمولة الموقع',
                    'website_commission.numeric' => 'نسبة العمولة يجب ان تكون رقمًا',
                    'website_commission.min' => 'نسبة العمولة يجب ان تكون بين 0 و 100',
                    'website_commission.max' => 'نسبة العمولة يجب ان تكون بين 0 و 100',
                    'repeat_client_threshold.required' => 'من فضلك ادخل عدد الطلبات لاعتبار العميل متكررًا',
                    'repeat_client_threshold.integer' => 'عدد الطلبات يجب ان يكون رقمًا صحيحًا',
                    'repeat_client_threshold.min' => 'عدد الطلبات يجب ان يكون 2 او اكثر',
                    'repeat_client_threshold.max' => 'عدد الطلبات يجب ان لا يتجاوز 100',
                    'client_count_mode.in' => 'اختر ما يُحسب كتواصل للعميل من القائمة',
                    'stage_fee_percent.max' => 'نسبة رسوم EasyKash يجب ألا تتجاوز 20%',
                    'stage_fee_fixed.max' => 'الرسوم الثابتة يجب ألا تتجاوز 1000 ج.م',
                ];
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

                if ($request->hasFile('hero_image')) {
                    if(isset($setting['hero_image']) && $setting['hero_image'] !='' && $setting['hero_image'] !=null){
                        $old_hero = public_path('assets/uploads/public_setting/' . $setting['hero_image']);
                        if (file_exists($old_hero)) {
                            unlink($old_hero);
                        }
                    }
                    $heroImage = $this->saveImage($request->hero_image, public_path('assets/uploads/public_setting'));
                    $setting->update(['hero_image' => $heroImage]);
                }

                $setting->update([
                    'website_title' => $data['website_title'],
                    'website_desc' => $data['website_desc'],
                    'website_color' => $data['website_color'],
                    'website_commission' => $data['website_commission'],
                    'repeat_client_threshold' => $data['repeat_client_threshold'],
                    'client_count_mode' => $data['client_count_mode'] ?? 'requests',
                    'client_tags_overlap' => $request->boolean('client_tags_overlap'),
                    'stage_fee_percent' => (float) ($data['stage_fee_percent'] ?? 0),
                    'stage_fee_fixed' => (float) ($data['stage_fee_fixed'] ?? 0),
                    'request_notification_emails' => collect(NotificationSettings::EMAILS)
                        ->map(fn ($meta, $key) => in_array($key, (array) $request->input('request_emails', []), true))
                        ->all(),
                    'hero_title_1' => $data['hero_title_1'] ?? '',
                    'hero_title_2' => $data['hero_title_2'] ?? '',
                    'hero_title_3' => $data['hero_title_3'] ?? '',
                    'hero_subtitle' => $data['hero_subtitle'] ?? '',
                    'hero_overlay_color' => $data['hero_overlay_color'] ?? '#00000057',
                ]);

                return $this->success_message(' تم تعديل الاعدادات بنجاح  ');
            } catch (\Exception $e) {
                return $this->exception_message($e);
            }
        }
    }

    public function gmailSettings()
    {
        $setting = Setting::first();
        $gmailService = app(GmailService::class);
        $authUrl = $gmailService->getAuthUrl();
        $isConnected = $gmailService->isReady();
        return view('admin.email.gmail_settings', compact('setting', 'authUrl', 'isConnected'));
    }

    public function gmailUpdate(Request $request)
    {
        $request->validate([
            'gmail_credentials_json' => 'required|json',
        ], [
            'gmail_credentials_json.required' => 'محتوى JSON الخاص بـ Google API مطلوب',
            'gmail_credentials_json.json' => 'يرجى إدخال JSON صحيح',
        ]);

        $setting = Setting::first();
        $setting->gmail_credentials_json = $request->gmail_credentials_json;
        $setting->save();

        return redirect()->route('admin.email.gmail.settings')
            ->with('success', 'تم حفظ بيانات Google API بنجاح. يرجى تفويض الوصول.');
    }

    public function gmailCallback(Request $request)
    {
        $request->validate(['code' => 'required']);

        $gmailService = app(GmailService::class);
        $success = $gmailService->handleCallback($request->code);

        if ($success) {
            return redirect()->route('admin.email.gmail.settings')
                ->with('success', 'تم ربط Gmail بنجاح! يمكنك الآن الإرسال عبر Gmail API.');
        }

        return redirect()->route('admin.email.gmail.settings')
            ->with('error', 'فشل الربط مع Gmail. يرجى المحاولة مرة أخرى.');
    }

    public function gmailDisconnect()
    {
        $setting = Setting::first();
        $setting->gmail_token_json = null;
        $setting->save();

        return redirect()->route('admin.email.gmail.settings')
            ->with('success', 'تم فصل Gmail بنجاح.');
    }

    public function sheetsAuth()
    {
        $sheetsService = app(GoogleSheetsService::class);
        $authUrl = $sheetsService->getAuthUrl();

        if (!$authUrl) {
            return redirect()->route('admin.email.gmail.settings')
                ->with('error', 'يرجى إدخال بيانات Google API أولاً في إعدادات Gmail.');
        }

        return redirect($authUrl);
    }

    public function sheetsCallback(Request $request)
    {
        $request->validate(['code' => 'required']);

        $sheetsService = app(GoogleSheetsService::class);
        $success = $sheetsService->handleCallback($request->code);

        if ($success) {
            return redirect()->route('admin.email.lists.index')
                ->with('success', 'تم ربط Google Sheets بنجاح! يمكنك الآن استيراد جهات الاتصال.');
        }

        return redirect()->route('admin.email.gmail.settings')
            ->with('error', 'فشل الربط مع Google Sheets. يرجى المحاولة مرة أخرى.');
    }
}
