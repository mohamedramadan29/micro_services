<?php

namespace App\Http\Controllers;

use App\Http\Traits\Message_Trait;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    use Message_Trait;

    public function index()
    {
        return view('website.user.dashboard');
    }

    public function reviews()
    {
        return view('website.user.reviews');
    }

    public function update(Request $request)
    {
        $data = $request->all();
        if ($request->isMethod('post')) {

        }
        return view('website.user.update');
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
//                    if (!empty(Session::get('session_id'))) {
//                        $user_id = Auth::user()->id;
//                        $session_id = Session::get('session_id');
//                        Cart::where('session_id', $session_id)->update([
//                            'user_id' => $user_id
//                        ]);
//                    }
                    return \redirect('user/dashboard');
                } else {
                    return Redirect::back()->withInput()->withErrors('لا يوجد حساب بهذه البيانات  ');
                }
            } catch (\Exception $e) {
                return $this->exception_message($e);
            }
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

    public function chat()
    {
        return view('website.user.chat');
    }
}
