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

    /** «لوحتي»: what this account should do next, built for clients and for freelancers. */
    public function index()
    {
        $user = Auth::user();
        $contracts = \App\Models\Contract::involving($user)
            ->whereIn('status', [\App\Models\Contract::STATUS_AGREEMENT, \App\Models\Contract::STATUS_ACTIVE])
            ->with(['client', 'freelancer', 'stages.deliveries', 'agreements', 'disputes'])->latest('id')->limit(5)->get();
        $data = ['user' => $user, 'contracts' => $contracts, 'commission' => \App\Services\Payments\RequestPaymentService::commissionRate()];

        if ($user->isFreelancer()) {
            $offers = \App\Models\RequestOffer::where('freelancer_id', $user->id);
            $data += [
                'checklist' => $user->freelancerChecklist(),
                'stats' => [
                    'matching' => \App\Models\ServiceRequest::forFreelancer($user)->whereDoesntHave('offers', fn ($q) => $q->where('freelancer_id', $user->id))->count(),
                    'active_offers' => (clone $offers)->whereIn('status', \App\Models\RequestOffer::ACTIVE_STATUSES)->count(),
                    'won' => (clone $offers)->where('status', \App\Models\RequestOffer::STATUS_ACCEPTED)->count(),
                    'contracts' => $contracts->count(),
                ],
                'latestOffers' => (clone $offers)->with('request')->latest('id')->limit(5)->get(),
                'services' => Service::with('category')->where('user_id', $user->id)->latest('id')->limit(6)->get(),
            ];
        } else {
            $requests = \App\Models\ServiceRequest::where('user_id', $user->id);
            $data += [
                'stats' => [
                    'open' => (clone $requests)->whereIn('status', [\App\Models\ServiceRequest::STATUS_OPEN, \App\Models\ServiceRequest::STATUS_INQUIRY])->count(),
                    'in_progress' => (clone $requests)->where('status', \App\Models\ServiceRequest::STATUS_IN_PROGRESS)->count(),
                    'completed' => (clone $requests)->where('status', \App\Models\ServiceRequest::STATUS_COMPLETED)->count(),
                    'new_offers' => \App\Models\RequestOffer::where('status', \App\Models\RequestOffer::STATUS_NEW)->whereHas('request', fn ($q) => $q->where('user_id', $user->id))->count(),
                ],
                'latestRequests' => (clone $requests)->withCount(['offers as offers_count' => fn ($q) => $q->where('status', '!=', \App\Models\RequestOffer::STATUS_WITHDRAWN)])
                    ->latest('id')->limit(5)->get(),
                'services' => Service::with('category')->where('user_id', $user->id)->latest('id')->limit(6)->get(),
            ];
        }

        return view('website.user.dashboard', $data);
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
                    'info' => 'nullable|string|max:1000',
                    'job_title' => 'nullable|string|max:100',
                    'phone' => 'required|unique:users,phone,' . $user->id,
                    // A freelancer must keep a WhatsApp number (client file «تعديلات», item 11).
                    'whatsapp' => [empty($user->whatsapp) ? 'required_if:account_type,' . User::TYPE_FREELANCER : 'nullable', 'nullable', 'string', 'max:30', User::WHATSAPP_RULE],
                
                    // «ملفك كمستقل» (merged from the old my/freelancer-profile page).
                    'availability' => 'nullable|in:' . implode(',', array_keys(User::AVAILABILITY)),
                    'languages' => 'nullable|string|max:255',
                    'starting_price' => 'nullable|numeric|min:0|max:1000000',
                ];

                // The password changes only when a new one is typed. Browsers often autofill the current
                // password field, so a filled «current password» alone must not ask for a new one.
                $changingPassword = filled($request->input('new_password'));
                if ($changingPassword) {
                    if (! filled($request->input('old_password')) || ! Hash::check((string) $request->input('old_password'), $user->password)) {
                        return Redirect::back()->withInput($request->except(['old_password', 'new_password', 'confirm_password']))
                            ->withErrors(['old_password' => 'لتغيير كلمة المرور اكتب كلمة المرور الحالية الصحيحة']);
                    }
                    $rules['new_password'] = 'required|min:8';
                    $rules['confirm_password'] = 'required|same:new_password';
                }

                // A freelancer's photo is his professional photo (shown to clients), so it must be sharp.
                $asFreelancer = $user->account_type !== User::TYPE_ADMIN && ($request->input('account_type') === User::TYPE_FREELANCER);
                if ($request->hasFile('image')) {
                    $rules['image'] = $asFreelancer ? \App\Support\FreelancerPhoto::RULE : 'image|mimes:jpeg,png,jpg,webp,gif|max:2048';
                }

                $messages = [
                    'name.required' => 'من فضلك ادخل الاسم',
                    'account_type.required' => 'من فضلك حدد نوع الحساب',
                    'account_type.in' => 'نوع الحساب غير صحيح',
                    'email.required' => 'من فضلك ادخل البريد الالكتروني',
                    'email.email' => 'من فضلك ادخل البريد الالكتروني بشكل صحيح',
                    'email.unique' => 'البريد الالكتروني متواجد من قبل من فضلك ادخل بريد الكتروني جديد',
                    'info.max' => 'النبذة طويلة جدًا (1000 حرف كحد أقصى)',
                    'whatsapp.required_if' => 'رقم واتساب مطلوب لحسابات المستقلين',
                    'whatsapp.regex' => 'رقم واتساب غير صحيح، اكتبه مع رمز الدولة',
                    'phone.required' => 'من فضلك ادخل رقم الهاتف',
                    'phone.unique' => 'رقم الهاتف مستخدم بالفعل من فضلك ادخل رقم هاتف جديد',
                    'new_password.required' => 'من فضلك ادخل كلمة المرور الجديدة',
                    'new_password.min' => 'كلمة المرور يجب ان تكون اكثر من 8 احرف ',
                    'confirm_password.same' => 'من فضلك يجب تاكيد كلمة المرور بشكل صحيح',
                    'image.image' => 'من فضلك اختر ملف صورة صالح',
                    'image.mimes' => 'نوع الصورة يجب أن يكون jpeg, png, jpg, webp, gif',
                    'image.max' => 'حجم الصورة كبير جدًا',
                    'image.dimensions' => 'الصورة صغيرة: أقل مقاس 400×400 بكسل حتى تظهر واضحة',
                
                    'availability.in' => 'حالة التوفر غير صحيحة',
                    'starting_price.numeric' => 'السعر يجب أن يكون رقمًا',
                    'starting_price.min' => 'السعر لا يكون أقل من صفر',
                ];

                $validator = Validator::make($data, $rules, $messages);

                if ($validator->fails()) {
                    return Redirect::back()->withInput()->withErrors($validator);
                }
                if ($user->account_type !== User::TYPE_ADMIN && $data['account_type'] !== $user->account_type && ($busy = $this->workInProgress($user))) {
                    return Redirect::back()->withInput()->withErrors(['account_type' => 'لا يمكن تغيير نوع الحساب الآن: ' . $busy . '. أنهِها أولًا أو تواصل مع الدعم.']);
                }

                // تحديث الصورة إذا تم رفعها
                if ($request->hasFile('image')) {
                    // The professional photo is cut from the upload first: saving the account image moves
                    // the uploaded file away, so the other order could no longer read it.
                    if ($asFreelancer) {
                        try {
                            \App\Support\FreelancerPhoto::replace($user, $request->file('image'));
                        } catch (\RuntimeException $e) {
                            return Redirect::back()->withInput()->withErrors(['image' => $e->getMessage()]);
                        }
                    }

                    $oldImage = public_path('assets/uploads/users_image/' . $user->image);
                    if ($user->image && file_exists($oldImage)) {
                        @unlink($oldImage);
                    }

                    $fileimage = $this->saveImage($request->file('image'), public_path('assets/uploads/users_image'));
                    $user->image = $fileimage;
                }

                // تحديث كلمة المرور إذا تم إدخال كلمة مرور جديدة
                if ($changingPassword) {
                    $user->password = Hash::make($data['new_password']);
                }

                // An admin keeps the admin type (the form only offers public types); a client who
                // becomes a freelancer starts «pending» like a new one; the other way round keeps nothing.
                $type = $user->account_type === User::TYPE_ADMIN ? User::TYPE_ADMIN : $data['account_type'];
                $wasFreelancer = $user->account_type === User::TYPE_FREELANCER;
                $extra = [];
                if ($type === User::TYPE_FREELANCER && $user->account_type !== User::TYPE_FREELANCER) {
                    $extra['freelancer_status'] = User::FREELANCER_PENDING;
                }
                if (filled($data['whatsapp'] ?? null)) {
                    $extra['whatsapp'] = \App\Support\Phone::normalize((string) $data['whatsapp']);
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
                    'info' => $data['info'] ?? null,
                    'account_type' => $type,
                    'job_title' => $data['job_title'] ?? null,
                ]);
                // One bio for a freelancer: «النبذة التعريفية» here is the bio clients see.
                if ($type === User::TYPE_FREELANCER && filled($data['info'] ?? null)) {
                    $extra['bio'] = $data['info'];
                }
                // A freelancer who stays a freelancer edits his public profile here too; a rejected one
                // (or one never reviewed) is sent back to the team's review by saving.
                $resubmitted = false;
                if ($type === User::TYPE_FREELANCER && $wasFreelancer) {
                    if ($request->has('availability')) {
                        $extra['availability'] = $data['availability'] ?: 'available';
                        $extra['languages'] = filled($data['languages'] ?? null) ? $data['languages'] : null;
                        $extra['starting_price'] = filled($data['starting_price'] ?? null) ? $data['starting_price'] : null;
                    }
                    if ($user->freelancer_status === User::FREELANCER_REJECTED || ! $user->freelancer_status) {
                        $extra['freelancer_status'] = User::FREELANCER_PENDING;
                        $resubmitted = true;
                    }
                }
                if ($extra) {
                    $user->forceFill($extra)->save(); // verification/approval columns are not mass-assignable
                }
                if ($resubmitted) {
                    \Illuminate\Support\Facades\Notification::send(User::activeAdmins()->get(), new \App\Notifications\RequestUpdateNotification(
                        \App\Support\Msg::t('admin.freelancer_pending_review', ['name' => $user->name]),
                        '/admin/freelancers/' . $user->id,
                        'admin_alerts'
                    ));
                }
                if ($type === User::TYPE_FREELANCER) {
                    $user->fresh()->markFreelancerProfileCompleted();
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

        return view('website.user.update', ['user' => $user]);
    }

    /** Why the account type cannot change now (open work on the current side), or null. */
    private function workInProgress(User $user): ?string
    {
        $contracts = \App\Models\Contract::whereIn('status', [\App\Models\Contract::STATUS_AGREEMENT, \App\Models\Contract::STATUS_ACTIVE]);
        if ($user->isFreelancer()) {
            if ((clone $contracts)->where('freelancer_id', $user->id)->exists()
                || \App\Models\RequestOffer::where('freelancer_id', $user->id)->where('status', \App\Models\RequestOffer::STATUS_ACCEPTED)
                    ->whereHas('request', fn ($q) => $q->where('status', \App\Models\ServiceRequest::STATUS_IN_PROGRESS))->exists()) {
                return 'عندك أعمال قيد التنفيذ كمستقل';
            }
        } elseif ($user->isClient()) {
            if ((clone $contracts)->where('client_id', $user->id)->exists()
                || \App\Models\ServiceRequest::where('user_id', $user->id)->whereIn('status', [\App\Models\ServiceRequest::STATUS_OPEN, \App\Models\ServiceRequest::STATUS_INQUIRY, \App\Models\ServiceRequest::STATUS_IN_PROGRESS])->exists()) {
                return 'عندك طلبات مفتوحة أو قيد التنفيذ كعميل';
            }
        }

        return null;
    }


    /** A freelancer's public page: who he is, his specializations, published works, clients' ratings and services. */
    public function show_profile($username)
    {
        $user = $this->publicProfile($username);
        $user = User::whereKey($user->id)->withFreelancerStats()->with(['specializations' => fn ($q) => $q->with('category:id,name')->orderBy('name')])->first();
        $viewer = Auth::user();
        $isOwner = $viewer && (int) $viewer->id === (int) $user->id;

        $works = $user->portfolios()->published()->with('Category:id,name')->latest()->limit(24)->get();
        $ratings = $user->receivedRatings()->with('rater:id,name')->latest()->limit(30)->get();
        $distribution = $user->receivedRatings()->selectRaw('stars, COUNT(*) as total')->groupBy('stars')->pluck('total', 'stars');
        $services = Service::with('category')->where('user_id', $user->id)
            ->when(! $isOwner, fn ($q) => $q->where('status', 1))->latest('id')->get();
        // The owner and admins may open a profile visitors cannot see yet: say so on the page.
        $listed = User::listedFreelancers()->whereKey($user->id)->exists();
        $firstService = $user->specializations->first();

        return view('website.user_profiles.index', compact('user', 'works', 'ratings', 'distribution', 'services', 'listed', 'isOwner', 'firstService'));
    }

    /** The old services tab is a section of the profile now. */
    public function user_services($username)
    {
        $user = $this->publicProfile($username);

        return redirect(url('user/' . $user->user_name) . '#services', 301);
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
        $userId = Auth::id();
        // One history: money movements (work payouts, refunds, payments held for work, withdrawals)
        // from wallet_transactions, plus EasyKash top-ups (kept in payment_transactions).
        $movements = \App\Models\WalletTransaction::where('user_id', $userId)->latest('id')->limit(200)->get()
            ->map(fn ($t) => ['type' => $t->type, 'amount' => (float) $t->amount, 'description' => $t->description, 'at' => $t->created_at]);
        $topUps = PaymentTransaction::where('user_id', $userId)->whereIn('payment_status', ['succeeded', 'paid'])->latest('id')->limit(200)->get()
            ->map(fn ($c) => ['type' => 'topup', 'amount' => (float) $c->payment_amount - (float) $c->payment_fee, 'description' => 'شحن رصيد عبر EasyKash', 'at' => $c->created_at]);
        $history = $movements->concat($topUps)->sortByDesc('at')->values();
        $withdrawals = WithDraw::where('user_id', $userId)->latest('id')->get();
        $pendingWithdraw = (float) $withdrawals->where('status', 0)->sum('amount');

        return view('website.user.balance', compact('history', 'withdrawals', 'pendingWithdraw'));
    }
}
