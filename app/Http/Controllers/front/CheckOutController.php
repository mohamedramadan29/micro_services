<?php

namespace App\Http\Controllers\front;

use App\Http\Controllers\Controller;
use App\Http\Traits\Message_Trait;
use App\Models\admin\Service;
use App\Models\front\Cart;
use App\Models\OnlinePayment;
use App\Services\Payments\OnlinePaymentService;
use App\Services\Payments\Purposes\ServiceOrderPurpose;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;

class CheckOutController extends Controller
{
    use Message_Trait;

    public function index()
    {
        [$items, $total] = $this->payableCart();
        if ($items->isEmpty()) {
            return redirect('cart')->withErrors('السلة فارغة');
        }

        return view('website.checkout', compact('items', 'total'));
    }

    /** Pay the whole cart at EasyKash; the order is created and the cart emptied once EasyKash confirms it. */
    public function order(OnlinePaymentService $payments)
    {
        [$items, $total] = $this->payableCart();
        if ($items->isEmpty()) {
            return redirect('cart')->withErrors('السلة فارغة');
        }

        $lines = $items->map(fn (Cart $item) => self::line($item->serviceData))->all();

        try {
            return redirect()->away($payments->start(OnlinePayment::PURPOSE_SERVICE_ORDER, Auth::user(), $total,
                'طلب ' . count($lines) . ' خدمة من السلة',
                ServiceOrderPurpose::payload($lines, ['cart_ids' => $items->pluck('id')->all(), 'back_url' => url('checkout')])));
        } catch (\Throwable $e) {
            return $this->payment_error($e);
        }
    }

    /** "اطلب الخدمة": pay one service at EasyKash; the order is created once EasyKash confirms it. */
    public function create_order(Request $request, OnlinePaymentService $payments)
    {
        $service = Service::findOrFail($request->input('service_id'));
        if ((int) $service->user_id === (int) Auth::id()) {
            return Redirect::back()->withErrors(['error' => 'لا يمكنك طلب خدمتك الخاصة']);
        }

        try {
            return redirect()->away($payments->start(OnlinePayment::PURPOSE_SERVICE_ORDER, Auth::user(), (float) $service->price, 'طلب الخدمة: ' . $service->name,
                ServiceOrderPurpose::payload([self::line($service)], ['back_url' => url()->previous()])));
        } catch (\Throwable $e) {
            return $this->payment_error($e);
        }
    }

    /**
     * The logged-in user's cart items that can still be bought (service exists,
     * not their own), with the total from current service prices — never from
     * what was stored in the cart.
     */
    private function payableCart(): array
    {
        $items = Cart::with('serviceData', 'userData')->where('user_id', Auth::id())->get()
            ->filter(fn (Cart $item) => $item->serviceData && (int) $item->serviceData->user_id !== (int) Auth::id() && (float) $item->serviceData->price > 0)
            ->values();

        return [$items, round((float) $items->sum(fn (Cart $item) => (float) $item->serviceData->price), 2)];
    }

    private static function line(Service $service): array
    {
        return ['service_id' => $service->id, 'service_name' => $service->name, 'seller_id' => (int) $service->user_id, 'price' => (float) $service->price];
    }
}
