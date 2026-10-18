<?php

namespace App\Http\Controllers\front;

use Illuminate\Http\Request;
use App\Models\admin\Package;
use App\Http\Traits\Message_Trait;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\OnlinePayment;
use App\Services\Payments\OnlinePaymentService;

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


    public function subscribePlan(Request $request, $id, OnlinePaymentService $payments)
    {
        $user = Auth::user();
        if (!$user) {
            return to_route("user_login");
        }
        $package = Package::findOrFail($id);

        try {
            return redirect()->away($payments->start(OnlinePayment::PURPOSE_PACKAGE, $user, (float) $package->price, 'اشتراك في خطة ' . $package->name, [
                'package_id' => $package->id,
                'package_name' => $package->name,
                'back_url' => route('packages.index'),
            ]));
        } catch (\Throwable $e) {
            return $this->payment_error($e);
        }
    }
}
