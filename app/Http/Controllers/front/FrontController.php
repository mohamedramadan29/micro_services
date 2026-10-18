<?php

namespace App\Http\Controllers\front;

use App\Models\User;
use Illuminate\Http\Request;
use App\Models\admin\Service;
use App\Models\admin\Category;
use App\Models\admin\Consultant;
use App\Models\admin\Setting;
use App\Models\admin\SubCategory;
use App\Http\Traits\Message_Trait;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;

class FrontController extends Controller
{
    use Message_Trait;

    public function index()
    {
        // Homepage cards need stored blocks (stable ids) for click counting from the first visit.
        app(\App\Services\Content\ContentManager::class)->ensureSeeded('home.cards');
        $main_categories = Category::where('status', 1)->where('home_page', 1)->limit(6)->get();
        $categories = Category::where('status', 1)->get();
        $sub_categories = SubCategory::where('status', 1)->where('home_page', 1)->limit(8)->get();
        $services = Service::where('status',1)->latest()->limit(4)->get();
        //dd($services);
        //dd($main_categories);
        $setting = Setting::first();
        return view('website.index', compact('main_categories', 'sub_categories','categories','services','setting'));
    }

    public function service_details($id, $slug)
    {
        $service = Service::with('user')->where('id', $id)->first();
        $more_servicess = Service::where('cat_id', $service['cat_id'])->where('id', '!=', $service['id'])->OrderBy('id')->limit('6')->get();

        /////// Make Notification Is Read
        ///
        if (Auth::check()) {
            $notification_type = 'App\Notifications\AcceptJobFromAdmin';
            $notifications = Auth::user()->unreadNotifications->where('type', $notification_type);
            foreach ($notifications as $notification) {
                $notification->markAsRead();
            }
        }


        return view('website.service-details', compact('service', 'more_servicess'));
    }

    public function categories()
    {
        $categories = Category::with('subCategories')->where('status', 1)->paginate(12);
        // dd($categories);
        return view('website.categories', compact('categories'));
    }

    public function sub_categories($slug)
    {
        $category = Category::where('slug', $slug)->first();
        $category_id = $category['id'];
        if ($category) {
            $sub_categories = SubCategory::where('parent_id', $category_id)->where('status',1)->paginate(12);
            return view('website.sub-categories', compact('category', 'sub_categories'));
        } else {
            abort(404);
        }
    }

    public function category_services(Request $request, $slug)
    {
       // $category = Category::where('slug', $slug)->first();
       $category = SubCategory::where('slug', $slug)->first();
        abort_unless($category, 404);

        $category_id = $category['id'];

       // $query = Service::where('cat_id', $category_id)->where('status', 1);
        $query = Service::where('sub_cat_id', $category_id)->where('status', 1);
        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->input('search') . '%');
        }
        if ($category) {
            $services = $query->paginate(12);
            return view('website.category-services', compact('services', 'category'));
        } else {
            abort(404);
        }
    }


    ///////////////////////// Start Forget Password //////////////////
    ///
    ///

    /**
     * Password reset (rewritten 2026-10-17: the old link was just base64(email) and the
     * update step trusted the posted email — anyone could set any account's password).
     * Now: a random token, stored hashed in password_reset_tokens, valid 60 minutes, one use.
     */
    public function forget_password(Request $request)
    {
        if ($request->isMethod('post')) {
            $data = $request->validate(['email' => 'required|email|max:255'], ['email.required' => 'من فضلك ادخل البريد الالكتروني', 'email.email' => 'البريد الإلكتروني غير صحيح']);
            $email = $data['email'];
            $user = User::where('email', $email)->first();
            if (! $user) {
                return Redirect::back()->withErrors(['للاسف لا يوجد حساب بهذة البيانات ']);
            }
            $token = \Illuminate\Support\Str::random(64);
            DB::table('password_reset_tokens')->updateOrInsert(['email' => $email], ['token' => Hash::make($token), 'created_at' => now()]);
            $MessageDate = ['code' => $token . '|' . base64_encode($email)];
            Mail::send('website.emails.UserChangePasswordMail', $MessageDate, function ($message) use ($email) {
                $message->to($email)->subject(' رابط تغير كلمة المرور ');
            });

            return $this->success_message(' تم ارسال رابط تغير كلمة المرور علي البريد الالكتروني  ');
        }

        return view('website.forget-password');
    }

    public function change_forget_password(Request $request, $code)
    {
        $parsed = $this->parseResetCode($code);
        if (! $parsed) {
            return redirect()->to('forget-password')->withErrors(['رابط تغيير كلمة المرور غير صالح أو انتهت صلاحيته (60 دقيقة). اطلب رابطًا جديدًا.']);
        }

        return view('website.change-password', ['email' => $parsed['email'], 'code' => $code]);
    }

    public function update_forget_password(Request $request)
    {
        $parsed = $this->parseResetCode((string) $request->input('code'));
        if (! $parsed) {
            return redirect()->to('forget-password')->withErrors(['رابط تغيير كلمة المرور غير صالح أو انتهت صلاحيته (60 دقيقة). اطلب رابطًا جديدًا.']);
        }
        $rules = [
            'password' => 'required|string|min:8|max:100',
            'confirm_password' => 'required|same:password',
        ];
        $messages = [
            'password.required' => ' من فضلك ادخل كلمة المرور  ',
            'password.min' => 'كلمة المرور 8 أحرف على الأقل',
            'confirm_password.required' => ' من فضلك اكد كلمة المرور ',
            'confirm_password.same' => ' من فضلك يجب تاكيد كلمة المرور بنجاح ',
        ];
        $validator = Validator::make($request->all(), $rules, $messages);
        if ($validator->fails()) {
            return Redirect::back()->withInput()->withErrors($validator);
        }
        $parsed['user']->update(['password' => Hash::make($request->input('password'))]);
        DB::table('password_reset_tokens')->where('email', $parsed['email'])->delete(); // one use

        return redirect()->to('login')->with('Success_message', '   تم تعديل كلمة المرور بنجاح سجل ذخولك الان ');
    }

    /** The user behind a reset link, or null when the token is unknown, wrong or older than 60 minutes. */
    private function parseResetCode(string $code): ?array
    {
        [$token, $encoded] = array_pad(explode('|', $code, 2), 2, '');
        $email = base64_decode($encoded, true);
        if ($token === '' || ! $email) {
            return null;
        }
        $row = DB::table('password_reset_tokens')->where('email', $email)->first();
        if (! $row || ! Hash::check($token, $row->token) || ! $row->created_at || now()->diffInMinutes($row->created_at, true) > 60) {
            return null;
        }
        $user = User::where('email', $email)->first();

        return $user ? ['email' => $email, 'user' => $user] : null;
    }

    ///////////////////////////////////////////////////
    public function terms()
    {
        return view('website.terms');
    }

    public function privacy_policy()
    {
        return view('website.privacy-policy');
    }


    public function search(Request $request)
    {

        if ($request->isMethod('get')) {
            $search = $request->input('search');
            $services = Service::with('category', 'user')->where('name', 'like', '%' . $search . '%')->paginate(12);
            $categories = Category::with('parents')->withCount('services')->where('status', '1')->get();
            return view('website.search', compact('services', 'search', 'categories'));
        }
    }

    public function getConsultantsByCategory(Request $request)
    {
        $categoryId = $request->get('category_id');
        $consultants = Consultant::where('specialization', $categoryId)->get(['id', 'name', 'image', 'bio']);

        return response()->json(['consultants' => $consultants]);
    }

}
