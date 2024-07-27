<?php

namespace App\Http\Controllers;

use App\Http\Traits\Message_Trait;
use App\Models\admin\Category;
use App\Models\admin\Service;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;

class FrontController extends Controller
{
    use Message_Trait;

    public function index()
    {
        return view('website.index');
    }

    public function services(Request $request)
    {
        $query = Service::with('category', 'user');
        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->input('search') . '%');
        }
        if ($request->has('cat_ids')){
            $cat_ids = $request->get('cat_ids');
            $query->whereIn('cat_id',$cat_ids);
        }
        // $services = Service::with('category', 'user')->where('status', '1')->paginate(12);
        $services = $query->where('status', '1')->paginate(12);
        $categories = Category::with('parents')->withCount('services')->where('status', '1')->get();
        return view('website.services', compact('services', 'categories'));


    }

    public function service_details($id, $slug)
    {
        $service = Service::with('user')->where('id', $id)->first();
        $more_servicess = Service::where('cat_id', $service['cat_id'])->where('id', '!=', $service['id'])->OrderBy('id')->limit('6')->get();
        return view('website.service-details', compact('service', 'more_servicess'));
    }

    public function categories()
    {
        $categories = Category::with('parents')->where('parent_id', '0')->paginate(12);
        //  dd($categories);
        return view('website.categories', compact('categories'));
    }

    public function sub_categories($slug)
    {
        $category = Category::where('slug', $slug)->first();
        $category_id = $category['id'];
        if ($category) {
            $sub_categories = Category::where('parent_id', $category_id)->paginate(12);
            return view('website.sub-categories', compact('category', 'sub_categories'));
        } else {
            abort(404);
        }
    }

    public function category_services($slug)
    {
        $category = Category::where('slug', $slug)->first();
        $category_id = $category['id'];
        if ($category) {
            $services = Service::where('cat_id', $category_id)->paginate(12);
            return view('website.category-services', compact('services', 'category'));
        } else {
            abort(404);
        }
    }


    ///////////////////////// Start Forget Password //////////////////
    ///
    ///

    public function forget_password(Request $request)
    {
        if
        ($request->isMethod('post')) {
            $data = $request->all();
            // dd($data);
            $email = $data['email'];
            $user = User::where('email', $email)->count();
            if ($user > 0) {
                ////////////////////// Send Forget Mail To User  ///////////////////////////////
                ///
                DB::beginTransaction();
                $email = $data['email'];
                $MessageDate = [
                    'code' => base64_encode($email)
                ];
                Mail::send('website.emails.UserChangePasswordMail', $MessageDate, function ($message) use ($email) {
                    $message->to($email)->subject(' رابط تغير كلمة المرور ');
                });
                DB::commit();
                return $this->success_message(' تم ارسال رابط تغير كلمة المرور علي البريد الالكتروني  ');
            } else {
                return Redirect::back()->withErrors(['للاسف لا يوجد حساب بهذة البيانات ']);
                // return $this->Error_message(' للاسف لا يوجد حساب بهذة البيانات  ');
            }
        }
        return view('website.forget-password');
    }

    public function change_forget_password(Request $request, $email)
    {
        $email = base64_decode($email);
        return view('website.change-password', compact('email'));
    }

    public function update_forget_password(Request $request)
    {
        if ($request->isMethod('post')) {
            $data = $request->all();
            // dd($data);
            $email = $data['email'];
            //dd($data);
            $usercount = User::where('email', $email)->count();
            if ($usercount > 0) {
                ////////// Start Change Password
                $user = User::where('email', $email)->first();
                $rules = [
                    'password' => 'required',
                    'confirm_password' => 'required|same:password'
                ];
                $messages = [
                    'password.required' => ' من فضلك ادخل كلمة المرور  ',
                    'confirm_password.required' => ' من فضلك اكد كلمة المرور ',
                    'confirm_password.same' => ' من فضلك يجب تاكيد كلمة المرور بنجاح '
                ];
                $validator = Validator::make($data, $rules, $messages);
                if ($validator->fails()) {
                    return Redirect::back()->withInput()->withErrors($validator);
                }
                $user->update([
                    'password' => Hash::make($data['password']),
                ]);
                return redirect()->to('login')->with('Success_message', '   تم تعديل كلمة المرور بنجاح سجل ذخولك الان ');
            } else {
                return Redirect::back()->withErrors(['للاسف لا يوجد حساب بهذة البيانات ']);
            }
        }
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
}
