<?php

namespace App\Services\Payments\Purposes;

use App\Http\Traits\Slug_Trait;
use App\Models\front\Cart;
use App\Models\front\Order;
use App\Models\front\OrderDetail;
use App\Models\OnlinePayment;
use App\Models\User;
use App\Notifications\NewOrderNotification;

/**
 * Buying freelancers' ready-made services: one service from its page, or the
 * whole cart. Payload `items` = [{service_id, service_name, seller_id, price}]
 * (prices from the database at checkout), `commission_rate` (taken from the
 * sellers' share) and, for the cart, `cart_ids` to empty once paid.
 */
class ServiceOrderPurpose extends SitePurpose
{
    use Slug_Trait;

    /** @param  array<int, array{service_id: int, service_name: string, seller_id: int, price: float}>  $items */
    public static function payload(array $items, array $extra = []): array
    {
        return ['items' => array_values($items), 'commission_rate' => \App\Services\Payments\RequestPaymentService::commissionRate()] + $extra;
    }

    public function paid(OnlinePayment $payment): void
    {
        $items = collect($payment->data('items', []));
        $rate = (float) $payment->data('commission_rate');
        $commission = fn ($price) => round((float) $price * $rate / 100, 2);
        $total = round((float) $items->sum('price'), 2);
        $totalCommission = round((float) $items->sum(fn ($item) => $commission($item['price'])), 2);
        $sellers = $items->pluck('seller_id')->unique();
        $number = (int) Order::max('order_number') + 1;

        $order = new Order();
        $order->order_number = $number;
        $order->user_buyer = $payment->user_id;
        // Sellers are on the order lines; the order names one only when there is one.
        $order->user_seller = $sellers->count() === 1 ? $sellers->first() : null;
        $order->order_price = $total;
        $order->website_commission = $totalCommission;
        $order->seller_commission = round($total - $totalCommission, 2);
        $order->status = 'جديد';
        $order->save();

        foreach ($items as $item) {
            $details = new OrderDetail();
            $details->order_id = $order->id;
            $details->order_number = $number;
            $details->user_buyer = $payment->user_id;
            $details->user_seller = $item['seller_id'];
            $details->service_id = $item['service_id'];
            $details->service_name = $item['service_name'];
            $details->service_price = $item['price'];
            $details->quantity = 1;
            $details->website_commission = $commission($item['price']);
            $details->seller_commission = round((float) $item['price'] - $commission($item['price']), 2);
            $details->status = 'جديد';
            $details->save();
        }

        if ($cartIds = $payment->data('cart_ids')) {
            Cart::whereIn('id', $cartIds)->where('user_id', $payment->user_id)->delete();
        }

        $buyer = $payment->user;
        if ($buyer) {
            foreach ($items as $item) {
                $seller = User::find($item['seller_id']);
                $this->afterCommit(fn () => $seller?->notify(new NewOrderNotification(
                    $buyer->id, $buyer->name, $buyer->user_name,
                    $item['service_id'], $this->CustomeSlug($item['service_name']), $item['service_name'],
                )));
            }
        }
    }

    protected function doneUrl(OnlinePayment $payment): string
    {
        return url('purches');
    }

    protected function paidMessage(): string
    {
        return 'تم طلب الخدمة بنجاح';
    }
}
