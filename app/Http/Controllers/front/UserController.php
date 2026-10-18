<?php

namespace App\Http\Controllers\front;

use App\Http\Controllers\Controller;
use App\Http\Traits\Message_Trait;
use App\Http\Traits\Slug_Trait;
use App\Http\Traits\Upload_Images;
use App\Models\admin\Service;
use App\Models\front\Cart;
use App\Models\front\OrderDetail;
use App\Models\front\PaymentTransaction;
use App\Models\front\WithDraw;
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
    use Slug_Trait;

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
                    'g-recaptcha-response' => ['required', 'captcha'],
                ];
                $customMessage = [
                    'email.required' => 'من فضلك ادخل البريد الإلكتروني',
                    'email.email' => 'من فضلك ادخل بريد الكتوني صحيح',
                    'password.required' => 'من فضلك ادخل كلمة المرور',
                    'g-recaptcha-response.required' => 'من فضلك قم بتأكيد أنك لست روبوتًا',
                    'g-recaptcha-response.captcha' => 'فشل التحقق من reCAPTCHA، يرجى المحاولة مرة أخرى'
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
                    if (Auth::user()->block_status == 0) {
                        Auth::logout();
                        return Redirect::back()->withInput()->withErrors('  تم حظر الحساب الخاص بك من فضلك تواصل مع الادارة  ');
                    }
                    // Update User Cart Put User Id
                    if (!empty(Session::get('session_id'))) {
                        $user_id = Auth::user()->id;
                        $session_id = Session::get('session_id');
                        Cart::where('session_id', $session_id)->update([
                            'user_id' => $user_id
                        ]);
                    }
                    $request->session()->regenerate();
                    return redirect()->intended('dashboard');
                } else {
                    return Redirect::back()->withInput()->withErrors('لا يوجد حساب بهذه البيانات  ');
                }
            } catch (\Exception $e) {
                return $this->exception_message($e);
            }
        }
        if (Auth::user()) {
            return \redirect('dashboard');
        }
        return view('website.login');
    }

    public function register(Request $request)
    {
        try {
            if ($request->isMethod('post')) {
                $data = $request->all();
                $rules = [
                    'name' => 'required',
                    'email' => 'required|email|unique:users,email',
                    'username' => 'required|unique:users,user_name',
                    'password' => 'required',
                    'confirm-password' => 'required|same:password',
                    'account_type' => 'required|in:' . User::TYPE_CLIENT . ',' . User::TYPE_FREELANCER,
                    'whatsapp' => ['required_if:account_type,' . User::TYPE_FREELANCER, 'nullable', 'string', 'max:30', User::WHATSAPP_RULE],
                    'g-recaptcha-response' => ['required', 'captcha']
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
                    'confirm-password.same' => ' من فضلك يجب تاكيد كلمة المرور بشكل صحيح ',
                    'account_type.required' => ' من فضلك اختر نوع الحساب: عميل او مستقل  ',
                    'account_type.in' => ' نوع الحساب غير صحيح  ',
                    'whatsapp.required_if' => 'رقم واتساب مطلوب لحسابات المستقلين',
                    'whatsapp.regex' => 'رقم واتساب غير صحيح، اكتبه مع رمز الدولة',
                    'g-recaptcha-response.required' => 'من فضلك قم بتأكيد أنك لست روبوتًا',
                    'g-recaptcha-response.captcha' => 'فشل التحقق من reCAPTCHA، يرجى المحاولة مرة أخرى'
                ];
                $validator = Validator::make($data, $rules, $messages);
                if ($validator->fails()) {
                    return Redirect::back()->withInput()->withErrors($validator);
                }
                DB::beginTransaction();
                $user = new User();
                $user->name = $data['name'];
                $user->user_name = $data['username'];
                $user->email = $data['email'];
                $user->password = Hash::make($data['password']);
                $user->account_type = $data['account_type'];
                if ($user->isFreelancer()) {
                    // Not listed and cannot send offers until the admin checks the WhatsApp number and approves.
                    $user->whatsapp = trim($data['whatsapp']);
                    $user->freelancer_status = User::FREELANCER_PENDING;
                }
                $user->save();
                // Email confirmation (signed link); for freelancers it is also their verification.
                \App\Support\EmailVerification::send($user);
                DB::commit();
                return $this->success_message('تم التسجيل بنجاح  :: من فضلك فعل الحساب تبعك من خلال البريد الالكتروني ');
                //return $this->success_message('تم انشاء حسابك بنجاح من فضلك سجل دخولك الان ');
            }
        } catch (\Exception $e) {
            if (DB::transactionLevel() > 0) {
                DB::rollBack();
            }
            return $this->exception_message($e);
        }
        if (Auth::user()) {
            return \redirect('/dashboard');
        }
        return view('website.register');
    }

    /** Old activation links (base64 of the email, forgeable) are refused; a signed one is sent on request. */
    public function UserConfirm($code)
    {
        return redirect('login')->withErrors('رابط التفعيل هذا قديم. اطلب رابط تأكيد جديدًا من صفحة الدخول.');
    }

    public function logout()
    {
        Auth::logout();
        return redirect('/');
    }

    public function update(Request $request)
    {
        $user = User::find(Auth::id());

        if ($request->isMethod('post')) {
            try {
                $data = $request->all();
                $rules = [
                    'name' => 'required',
                    'account_type' => 'required|in:' . implode(',', User::SELF_SELECTABLE_TYPES),
                    'email' => 'required|email|unique:users,email,' . $user->id,
                    'info' => 'required',
                    'phone' => 'required|unique:users,phone,' . $user->id,
                ];

                if ($request->has('old_password') && !empty($request->old_password)) {
                    if (!Hash::check($data['old_password'], $user->password)) {
                        return Redirect::back()->withInput()->withErrors(['كلمة المرور القديمة غير صحيحة']);
                    }
                    $rules['new_password'] = 'required|min:8';
                    $rules['confirm_password'] = 'required|same:new_password';
                }

                if ($request->hasFile('image')) {
                    $rules['image'] = 'image|mimes:jpeg,png,jpg,webp,gif|max:2048';
                }

                $messages = [
                    'name.required' => 'من فضلك ادخل الاسم',
                    'account_type.required' => 'من فضلك حدد نوع الحساب',
                    'account_type.in' => 'نوع الحساب غير صحيح',
                    'email.required' => 'من فضلك ادخل البريد الالكتروني',
                    'email.email' => 'من فضلك ادخل البريد الالكتروني بشكل صحيح',
                    'email.unique' => 'البريد الالكتروني متواجد من قبل من فضلك ادخل بريد الكتروني جديد',
                    'info.required' => 'من فضلك ادخل نبذة مختصرة عنك',
                    'phone.required' => 'من فضلك ادخل رقم الهاتف',
                    'phone.unique' => 'رقم الهاتف مستخدم بالفعل من فضلك ادخل رقم هاتف جديد',
                    'new_password.required' => 'من فضلك ادخل كلمة المرور الجديدة',
                    'new_password.min' => 'كلمة المرور يجب ان تكون اكثر من 8 احرف ',
                    'confirm_password.same' => 'من فضلك يجب تاكيد كلمة المرور بشكل صحيح',
                    'image.image' => 'من فضلك اختر ملف صورة صالح',
                    'image.mimes' => 'نوع الصورة يجب أن يكون jpeg, png, jpg, webp, gif',
                    'image.max' => 'حجم الصورة يجب ان لا يتجاوز ال2 ميجابايت',
                ];

                $validator = Validator::make($data, $rules, $messages);

                if ($validator->fails()) {
                    return Redirect::back()->withInput()->withErrors($validator);
                }

                // تحديث الصورة إذا تم رفعها
                if ($request->hasFile('image')) {
                    $oldImage = public_path('assets/uploads/users_image/' . $user->image);
                    if ($user->image && file_exists($oldImage)) {
                        @unlink($oldImage);
                    }

                    $fileimage = $this->saveImage($request->file('image'), public_path('assets/uploads/users_image'));
                    $user->image = $fileimage;
                }

                // تحديث كلمة المرور إذا تم إدخال كلمة مرور جديدة
                if ($request->has('new_password') && !empty($data['new_password'])) {
                    $user->password = Hash::make($data['new_password']);
                }

                // An admin keeps the admin type (the form only offers public types); a client who
                // becomes a freelancer starts «pending» like a new one; the other way round keeps nothing.
                $type = $user->account_type === User::TYPE_ADMIN ? User::TYPE_ADMIN : $data['account_type'];
                $extra = [];
                if ($type === User::TYPE_FREELANCER && $user->account_type !== User::TYPE_FREELANCER) {
                    $extra['freelancer_status'] = User::FREELANCER_PENDING;
                }
                // A new email must be confirmed again (it verifies the freelancer).
                $emailChanged = mb_strtolower(trim($data['email'])) !== mb_strtolower((string) $user->email);
                if ($emailChanged) {
                    $extra['email_verified_at'] = null;
                }

                // تحديث باقي البيانات
                $user->update([
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'phone' => $data['phone'],
                    'info' => $data['info'],
                    'account_type' => $type,
                    'job_title' => $data['job_title'] ?? $user->job_title,
                ]);
                if ($extra) {
                    $user->forceFill($extra)->save(); // verification/approval columns are not mass-assignable
                }
                if ($emailChanged) {
                    try {
                        \App\Support\EmailVerification::send($user->fresh());
                    } catch (\Throwable $e) {
                        report($e);
                    }
                }

                return $this->success_message('تم تحديث بياناتك بنجاح');
            } catch (\Exception $e) {
                return $this->exception_message($e);
            }
        }

        return view('website.user.update');
    }


    public function show_profile($username)
    {
        $user = $this->publicProfile($username);
        $services = Service::with('category')->where('user_id', $user->id)->get();

        return view('website.user_profiles.index', compact('user', 'services'));
    }

    public function user_services($username)
    {
        $user = $this->publicProfile($username);
        $services = Service::with('category')->where('user_id', $user->id)->get();

        return view('website.user_profiles.services', compact('user', 'services'));
    }

    /**
     * Public profiles are those of listed freelancers (approved, verified, active — same rule as the
     * directory); the owner and admins always see theirs. Anything else is a 404.
     */
    private function publicProfile(string $username): User
    {
        $user = User::where('user_name', $username)->firstOrFail();
        $viewer = Auth::user();
        $allowed = ($viewer && ((int) $viewer->id === (int) $user->id || ($viewer->account_type === User::TYPE_ADMIN && (int) $viewer->status === 1)))
            || ($user->isApprovedFreelancer() && (int) $user->status === 1 && (int) $user->block_status !== 0);
        abort_unless($allowed, 404);

        return $user;
    }

    public function chat()
    {
        return view('website.user.chat');
    }

    public function purches()
    {

        ////////// Get The Pruches
        ///
        $purches = OrderDetail::with('seller')->where('user_buyer', Auth::id())->get();
        // Generate slugs for each purchase
        foreach ($purches as $purche) {
            $purche->slug = $this->CustomeSlug($purche->service_name);
        }
        return view('website.user.purches', compact('purches'));
    }

    public function orders()
    {
        $orders = OrderDetail::with('buyer')->where('user_seller', Auth::id())->orderBy('id', 'DESC')->get();

        // Generate slugs for each purchase
        foreach ($orders as $order) {
            $order->slug = $this->CustomeSlug($order->service_name);
        }
        /////// Make Notification Is Read
        ///
        $notification_type = 'App\Notifications\NewOrderNotification';
        $notifications = Auth::user()->unreadNotifications->where('type', $notification_type);
        foreach ($notifications as $notification) {
            $notification->markAsRead();
        }
        return view('website.user.orders', compact('orders'));
    }

    public function balance()
    {
        $ChargeTransactions = PaymentTransaction::where('user_id', Auth::id())->whereIn('payment_status', ['succeeded', 'paid'])->get();
        $WithDrawTransactions = WithDraw::where('user_id', Auth::id())->get();
        $WithDrawLastOrder = WithDraw::where('user_id', Auth::id())->where('status', 0)->first();
        return view('website.user.balance', compact('ChargeTransactions', 'WithDrawTransactions', 'WithDrawLastOrder'));
    }
}
