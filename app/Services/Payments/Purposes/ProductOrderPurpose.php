<?php

namespace App\Services\Payments\Purposes;

use App\Models\admin\Product;
use App\Models\front\ProductOrder;
use App\Models\OnlinePayment;
use Illuminate\Support\Facades\Mail;

/** Buying a digital or physical product from the store. */
class ProductOrderPurpose extends SitePurpose
{
    public function paid(OnlinePayment $payment): void
    {
        $product = Product::find($payment->data('product_id'));

        $order = new ProductOrder();
        $order->user_id = $payment->user_id;
        $order->product_id = $payment->data('product_id');
        $order->product_name = $product?->name ?? $payment->data('product_name');
        $order->price = (float) $payment->amount;
        $order->name = $payment->data('name');
        $order->email = $payment->data('email');
        $order->phone = $payment->data('phone');
        $order->country = $payment->data('country');
        $order->city = $payment->data('city');
        $order->address = $payment->data('address');
        $order->order_status = 'تم الدفع';
        $order->save();

        if ($product && $product->type === 'digital' && $product->digital_file) {
            $email = $order->email;
            $data = [
                'name' => $order->name,
                'email' => $email,
                'purchasesPageLink' => route('user.products.purches'),
                'productName' => $product->name,
            ];
            $this->afterCommit(fn () => Mail::send('website.emails.DigitalProductMail', $data, function ($message) use ($email) {
                $message->to($email)->subject(' شراء المنتج الرقمي من نفذها   ');
            }));
        }
    }

    protected function doneUrl(OnlinePayment $payment): string
    {
        return route('user.products.purches');
    }

    protected function paidMessage(): string
    {
        return 'تمت عملية الشراء بنجاح.';
    }
}
