<?php

namespace App\Http\Controllers\front;

use Stripe\Stripe;
use Illuminate\Http\Request;
use Stripe\Checkout\Session;
use App\Models\admin\Package;
use App\Http\Traits\Message_Trait;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\admin\PackageSubscribe;
use Illuminate\Support\Facades\Redirect;
use Stripe\Exception\ApiErrorException;

class PackageController extends Controller
{
    use Message_Trait;

    // public function index()
    // {
    //     $packages = Package::all();
    //     return view("website.package.index", compact("packages"));
    // }

    public function index()
    {
        $packagesGrouped = Package::orderBy('title')->get()->groupBy('title');

        return view("website.package.index", compact("packagesGrouped"));
    }


    public function subscribePlan(Request $request, $id)
    {

        $user = Auth::user();
        if (!$user) {
            return to_route("user_login");
        }
        $package = Package::where('id', $id)->first();
        ############# Insert Package Subscribe #################

        if (!$package) {
            abort(404);
        }
        try {
            ########################### Redirect To Strip ############################
            Stripe::setApiKey(env('STRIPE_SECRET'));

            $session = Session::create([
                'payment_method_types' => ['card'],
                'line_items' => [
                    [
                        'price_data' => [
                            'currency' => 'usd',
                            'product_data' => ['name' => $package->name],
                            'unit_amount' => $package->price * 100,
                        ],
                        'quantity' => 1,
                    ]
                ],
                'mode' => 'payment',
                'metadata' => [
                    'user_id' => Auth::id(),
                    'name' => $user->name,
                    'email' => $user->email,
                    'package_id' => $package->id,
                    'package_name' => $package->name,
                ],
                'success_url' => route('package.order.success') . '?session_id={CHECKOUT_SESSION_ID}&package_id=' . $package->id,
                'cancel_url' => route('package.order.cancel'),
            ]);

            return redirect($session->url);
            ########################### IF Payment Done Add Package Subscribe ############

        } catch (\Exception $e) {
            return $this->exception_message($e);
        }
    }

    public function paymentSuccess(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return to_route("user_login");
        }

        try {
            Stripe::setApiKey(env('STRIPE_SECRET'));

            $session = Session::retrieve($request->session_id);
            if (!$session) {
                return Redirect::route('packages.index')->withErrors(['حدث خطأ أثناء التحقق من الدفع.']);
            }

            $metadata = $session->metadata;
            // dd($metadata);
            $package = Package::findOrFail($metadata->package_id);
            $package_price = $package->price;

            DB::beginTransaction();
            $subscribe = new PackageSubscribe();
            $subscribe->user_id =  $metadata->user_id;
            $subscribe->package_id = $package->id;
            $subscribe->package_name = $package->name;
            $subscribe->user_name = $user->name;
            $subscribe->price = $package->price;
            $subscribe->save();
            DB::commit();
            return $this->success_message(' تم الاشتراك في الخطة بنجاح سيتم التواصل معك في اقرب وقت ممكن  ');
        } catch (ApiErrorException $e) {
            return $this->exception_message($e);
        }
    }

    public function PaymentCancel(Request $request)
    {
        return $this->Error_message('تم الغاء الدفع');
    }
}
