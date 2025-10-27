<?php

namespace App\Http\Controllers\front;

use Stripe\Stripe;
use App\Models\User;
use Illuminate\Http\Request;
use Stripe\Checkout\Session;
use App\Models\admin\Product;
use App\Http\Traits\Message_Trait;
use App\Models\front\ProductOrder;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Stripe\Exception\ApiErrorException;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class ProductOrderController extends Controller
{
    use Message_Trait;


    public function store(Request $request)
    {
        try {
            $data = $request->all();
            $product = Product::findOrFail($data['product_id']);
            $isDigital = $product->type === 'digital';

            $rules = [
                'name' => 'required',
                'phone' => 'required',
                'email' => 'required|email',
                'product_id' => 'required',
            ];

            // إذا فيزيكال، أضف الشحن مطلوب
            if (!$isDigital) {
                $rules['country'] = 'required';
                $rules['city'] = 'required';
                $rules['address'] = 'required';
            }

            $messages = [
                'name.required' => ' من فضلك ادخل الاسم ',
                'phone.required' => ' من فضلك ادخل رقم الهاتف ',
                'email.required' => ' من فضلك ادخل البريد الالكتروني ',
                'email.email' => ' من فضلك ادخل بريد الكتروني بشكل صحيح ',
                'product_id.required' => 'يجب اختيار منتج للشراء',
                'country.required' => ' من فضلك ادخل الدولة ',
                'city.required' => ' من فضلك ادخل المدينة ',
                'address.required' => ' من فضلك ادخل العنوان بشكل كامل ',
            ];

            $validator = Validator::make($data, $rules, $messages);
            if ($validator->fails()) {
                return Redirect::back()->withInput()->withErrors($validator);
            }

            $product_price = $product->discount ? $product->discount : $product->price;

            Stripe::setApiKey(env('STRIPE_SECRET'));

            $metadata = [
                'user_id' => Auth::id(),
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'product_id' => $product->id,
                'product_type' => $product->type, // إضافة النوع
            ];

            // أضف الشحن إذا فيزيكال
            if (!$isDigital) {
                $metadata['country'] = $request->country;
                $metadata['city'] = $request->city;
                $metadata['address'] = $request->address;
            }

            $session = Session::create([
                'payment_method_types' => ['card', 'alipay'],
                'line_items' => [
                    [
                        'price_data' => [
                            'currency' => 'usd',
                            'product_data' => ['name' => $product->name],
                            'unit_amount' => $product_price * 100,
                        ],
                        'quantity' => 1,
                    ]
                ],
                'mode' => 'payment',
                'metadata' => $metadata,
                'success_url' => route('product.order.success') . '?session_id={CHECKOUT_SESSION_ID}&product_id=' . $product->id,
                'cancel_url' => route('product.order.cancel'),
            ]);

            return redirect($session->url);
        } catch (\Exception $e) {
            return $this->exception_message($e);
        }
    }


    public function paymentSuccess(Request $request)
    {
        try {
            Stripe::setApiKey(env('STRIPE_SECRET'));

            $session = Session::retrieve($request->session_id);
            if (!$session) {
                return Redirect::route('home')->withErrors(['حدث خطأ أثناء التحقق من الدفع.']);
            }

            $metadata = $session->metadata;
            $product = Product::findOrFail($metadata->product_id);
            $product_price = $product->discount ? $product->discount : $product->price;
            $isDigital = $product->type === 'digital';

            DB::beginTransaction();
            $order = new ProductOrder();
            $order->user_id = $metadata->user_id;
            $order->product_id = $metadata->product_id;
            $order->product_name = $product->name;
            $order->price = $product_price;
            $order->name = $metadata->name;
            $order->email = $metadata->email;
            $order->phone = $metadata->phone;

            // الشحن إذا فيزيكال
            if (!$isDigital) {
                $order->country = $metadata->country;
                $order->city = $metadata->city;
                $order->address = $metadata->address;
            }

            $order->order_status = 'تم الدفع';
            $order->save();

            // إذا رقمي، أرسل إيميل مع رابط صفحة المشتريات (مش رابط تنزيل مباشر)
            if ($isDigital && $product->digital_file) {
                $purchasesPageLink = route('user.products.purches'); // رابط صفحة المشتريات
                // Send Activation Email To User
                $email = $order['email'];
                $MessageDate = [
                    'name' => $order['name'],
                    "email" => $order['email'],
                    'purchasesPageLink' => $purchasesPageLink,
                    'productName' => $product->name,
                ];
                Mail::send('website.emails.DigitalProductMail', $MessageDate, function ($message) use ($email) {
                    $message->to($email)->subject(' شراء المنتج الرقمي من نفذها   ');
                });
            }

            DB::commit();
            return Redirect()->route('user.products.purches')->with('success', 'تمت عملية الشراء بنجاح.');
        } catch (ApiErrorException $e) {
            return $this->exception_message($e);
        }
    }

    // الدالة للتنزيل المحمي (تبقى كما هي، تستخدم في الصفحة)
    public function downloadProduct($orderId)
    {
        $user = Auth::user();
        if (!$user) {
            abort(403, 'غير مصرح لك بالوصول.');
        }

        $order = ProductOrder::where('id', $orderId)
            ->where('user_id', $user->id)
            ->where('order_status', 'تم الدفع')
            ->with('product')
            ->firstOrFail();

        if ($order->product->type !== 'digital' || !$order->product->digital_file) {
            abort(403, 'هذا المنتج غير رقمي أو لا يوجد ملف للتنزيل.');
        }

        $filePath = public_path('assets/uploads/digital_products/' . $order->product->digital_file);

        if (!file_exists($filePath)) {
            abort(404, 'الملف غير موجود.');
        }

        return Response::download($filePath, $order->product->name . '.' . pathinfo($order->product->digital_file, PATHINFO_EXTENSION))
            ->deleteFileAfterSend(false);
    }


    public function PaymentCancel()
    {
        return $this->Error_message('تم الغاء الدفع');
    }
}
