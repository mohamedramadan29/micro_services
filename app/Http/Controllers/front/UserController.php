<?php

namespace App\Http\Controllers\front;

use App\Http\Controllers\Controller;
use App\Http\Traits\Message_Trait;
use App\Http\Traits\Upload_Images;
use App\Models\admin\Service;
use App\Models\Cart;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    use Message_Trait;
    use Upload_Images;

    public function index()
    {
        $services = Service::with('category')->where('user_id', Auth::id())->get();
        return view('website.user.dashboard', compact('services'));
    }

    public function reviews()
    {
        return view('website.user.reviews');
    }

    public function login(Request $request)
    {
        if ($request->isMethod('post')) {
            try {
                $all_data = $request->all();
                $rules = [
                    'email' => 'required|email',
                    'password' => 'required',
                ];
                $customMessage = [
                    'email.required' => 'من فضلك ادخل البريد الإلكتروني',
                    'email.email' => 'من فضلك ادخل بريد الكتوني صحيح',
                    'password.required' => 'من فضلك ادخل كلمة المرور',
                ];
                $validator = Validator::make($all_data, $rules, $customMessage);
                if ($validator->fails()) {
                    return Redirect::back()->withInput()->withErrors($validator);
                }
                if (Auth::attempt(['email' => $all_data['email'], 'password' => $all_data['password']])) {
                    if (Auth::user()->status == 0) {
                        Auth::logout();
                        return Redirect::back()->withInput()->withErrors('  من فضلك يجب تفعيل الحساب الخاص بك اولا  ');
                    }
                    // Update User Cart Put User Id
                    if (!empty(Session::get('session_id'))) {
                        $user_id = Auth::user()->id;
                        $session_id = Session::get('session_id');
                        Cart::where('session_id', $session_id)->update([
                            'user_id' => $user_id
                        ]);
                    }
                    return \redirect('dashboard');
                } else {
                    return Redirect::back()->withInput()->withErrors('لا يوجد حساب بهذه البيانات  ');
                }
            } catch (\Exception $e) {
                return $this->exception_message($e);
            }
        }
        if (Auth::user()) {
            return \redirect('user/dashboard');
        }
        return view('website.login');
    }

    public function register(Request $request)
    {
        try {
            if ($request->isMethod('post')) {
                DB::beginTransaction();
                $data = $request->all();
                // dd($data);
                $rules = [
                    'name' => 'required',
                    'email' => 'required|email|unique:users,email',
                    'username' => 'required|unique:users,user_name',
                    'password' => 'required',
                    'confirm-password' => 'required|same:password',
                ];
                $messages = [
                    'name.required' => ' من فضلك ادخل الاسم  ',
                    'email.required' => ' من فضلك ادخل البريد الالكتروني  ',
                    'email.unique' => ' البريد الالكتروني مستخدم بالفعل من فضلك ادخل بريد الكتروني جديد  ',
                    'email.email' => ' من فضلك ادخل بريد الكتروني بشكل صحيح   ',
                    'username.required' => ' من فضلك ادخل اسم المستخدم  ',
                    'username.unique' => ' اسم المستخدم متواجد بالفعل من فضلك ادخل اسم مستخدم جديد  ',
                    'password.required' => ' من فضلك ادخل كلمة المرور  ',
                    'confirm-password.required' => ' من فضلك يجب تاكيد كلمة المرور بشكل صحيح  ',
                    'confirm-password.same' => ' من فضلك يجب تاكيد كلمة المرور بشكل صحيح '
                ];
                $validator = Validator::make($data, $rules, $messages);
                if ($validator->fails()) {
                    return Redirect::back()->withInput()->withErrors($validator);
                }
                $user = new User();
                $user->name = $data['name'];
                $user->user_name = $data['username'];
                $user->email = $data['email'];
                $user->password = Hash::make($data['password']);
                $user->save();
                // Send Activation Email To User
                $email = $data['email'];
                $MessageDate = [
                    'name' => $data['name'],
                    "email" => $data['email'],
                    'code' => base64_encode($email)
                ];
                Mail::send('website.emails.UserActivation', $MessageDate, function ($message) use ($email) {
                    $message->to($email)->subject(' تفعيل الحساب الخاص بك  ');
                });
                DB::commit();
                return $this->success_message('تم التسجيل بنجاح  :: من فضلك فعل الحساب تبعك من خلال البريد الالكتروني ');
                //return $this->success_message('تم انشاء حسابك بنجاح من فضلك سجل دخولك الان ');
            }
        } catch (\Exception $e) {
            return $this->exception_message($e);
        }
        if (Auth::user()) {
            return \redirect('user/dashboard');
        }
        return view('website.register');
    }

    public function UserConfirm($email)
    {
        $email = base64_decode($email);
        // check if this email exist in users or not
        $user_details = User::where('email', $email)->first();
        $userCount = User::where('email', $email)->count();
        if ($userCount > 0) {
            if ($user_details->status == 1) {
                $message = 'تم تفعيل البريد الالكتروني بالفعل ! سجل دخولك الان ';
                return redirect('login')->with('Error_Message', $message);
            } else {
                // Update User Status
                User::where('email', $email)->update(['status' => 1]);
                // Redirect User To Login/ Regitser Page With Message
                $message = 'تم تفعيل البريد الالكتروني الخاص بك يمكنك تسجيل الدخول الان ';
                return redirect('login')->with('Success_message', $message);
            }
        } else {
            abort(404);
        }
    }

    public function logout()
    {
        Auth::logout();
        return redirect('/');
    }

    public function update(Request $request)
    {
        $user = Auth::user();
        if ($request->isMethod('post')) {
            try {

                $data = $request->all();
                $rules = [
                    'name' => 'required',
                    'account_type' => 'required',
                    'email' => 'required|email|unique:users,email,' . $user->id,
                    'info' => 'required',
                    'phone' => 'required|unique:users,phone,' . $user->id,
                ];

                if ($request->has('old_password') && $request['old_password'] != '') {
                    // التحقق من صحة كلمة المرور القديمة
                    if (!Hash::check($data['old_password'], $user->password)) {
                        return Redirect::back()->withInput()->withErrors(['كلمة المرور القديمة غير صحيحة']);
                    }
                    $rules['new_password'] = 'required|min:8';
                    $rules['confirm_password'] = 'required|same:new_password';

                    $user->update(['password' => Hash::make($data['new_password'])]);
                }
                $messages = [
                    'name.required' => 'من فضلك ادخل الاسم',
                    'account_type.required' => 'من فضلك حدد نوع الحساب',
                    'email.required' => 'من فضلك ادخل البريد الالكتروني',
                    'email.email' => 'من فضلك ادخل البريد الالكتروني بشكل صحيح',
                    'email.unique' => 'البريد الالكتروني متواجد من قبل من فضلك ادخل بريد الكتروني جديد',
                    'info.required' => 'من فضلك ادخل نبذة مختصرة عنك',
                    'phone.required' => 'من فضلك ادخل رقم الهاتف',
                    'phone.unique' => 'رقم الهاتف مستخدم بالفعل من فضلك ادخل رقم هاتف جديد',
                    'new_password.required' => 'من فضلك ادخل كلمة المرور الجديدة',
                    'new_password.min' => 'كلمة المرور يجب ان تكون اكثر من 8 احرف ',
                    'confirm_password.same' => 'من فضلك يجب تاكيد كلمة المرور بشكل صحيح '
                ];
                if ($request->hasFile('image')) {
                    $rules['image'] = 'image|mimes:jpeg,png,jpg,webp,gif|max:2048';
                    $messages['image.image'] = 'من فضلك اختر ملف صورة صالح';
                    $messages['image.mimes'] = 'نوع الصورة يجب أن يكون jpeg, png, jpg,webp, gif';
                    $messages['image.max'] = ' حجم   الصورة يجب ان لا يتجاوز ال2m ';
                }

                if ($request->hasFile('image')) {
                    /////// Delete Old Image
                    ///
                    $oldImage = public_path('assets/uploads/users_image/' . $user['image']);
                    if (isset($oldImage) && $oldImage !='') {
                     //   unlink($oldImage);
                    }
                    $fileimage = $this->saveImage($request->image, public_path('assets/uploads/users_image'));
                    $user->update([
                        'image' => $fileimage,
                    ]);
                }
                $validator = Validator::make($data, $rules, $messages);
                if ($validator->fails()) {
                    return Redirect::back()->withInput()->withErrors($validator);
                }

                $user->update([
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'phone' => $data['phone'],
                    'info' => $data['info'],
                    'account_type' => $data['account_type'],
                    'job_title' => $data['job_title']

                ]);
                return $this->success_message('تم تحديث بياناتك بنجاح');
            } catch
            (\Exception $e) {
                return $this->exception_message($e);
            }
        } else {
            return view('website.user.update');
        }

    }

    public function show_profile()
    {
        $user = User::where('id', Auth::user()->id)->first();

        $services = Service::with('category')->where('user_id', Auth::id())->get();
        return view('website.user_profiles.index', compact('user', 'services'));
    }

    public function user_services($username)
    {
        $user = User::where('user_name', $username)->first();
        $services = Service::with('category')->where('user_id', $user->id)->get();
        return view('website.user_profiles.services', compact('user', 'services'));
    }

    public function chat()
    {
        return view('website.user.chat');
    }
}
