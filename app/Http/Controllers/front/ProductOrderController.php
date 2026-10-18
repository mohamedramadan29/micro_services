<?php

namespace App\Http\Controllers\front;

use App\Models\User;
use Illuminate\Http\Request;
use App\Models\admin\Product;
use App\Http\Traits\Message_Trait;
use App\Models\front\ProductOrder;
use App\Models\OnlinePayment;
use App\Services\Payments\OnlinePaymentService;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class ProductOrderController extends Controller
{
    use Message_Trait;


    /** Paid products go to EasyKash; the order is created once EasyKash confirms the payment. */
    public function store(Request $request, OnlinePaymentService $payments)
    {
        try {
            $data = $request->all();
            $product = Product::findOrFail($data['product_id']);
            $isDigital = $product->type === 'digital';

            $final_price = $product->discount > 0 ? $product->discount : $product->price;
            if ($final_price == 0) {
                return $this->processFreeOrder($product, $request);
            }
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

            $payload = [
                'product_id' => $product->id,
                'product_name' => $product->name,
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'back_url' => url()->previous(),
            ];
            // الشحن إذا فيزيكال
            if (!$isDigital) {
                $payload['country'] = $request->country;
                $payload['city'] = $request->city;
                $payload['address'] = $request->address;
            }

            return redirect()->away($payments->start(OnlinePayment::PURPOSE_PRODUCT_ORDER, Auth::user(), (float) $final_price, 'شراء المنتج ' . $product->name, $payload,
                ['name' => $request->name, 'email' => $request->email, 'mobile' => $request->phone]));
        } catch (\Throwable $e) {
            return $this->payment_error($e);
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


    ###################### Free Product Price ########################

    private function processFreeOrder($product, $request)
    {
        $isDigital = $product->type === 'digital';

        // فاليديشن بسيط للمنتجات المجانية
        $request->validate([
            'name' => 'required',
            'phone' => 'required',
            'email' => 'required|email',
        ] + (!$isDigital ? [
            'country' => 'required',
            'city' => 'required',
            'address' => 'required',
        ] : []));

        DB::beginTransaction();

        $order = new ProductOrder();
        $order->user_id = Auth::id() ?? null; // لو مش مسجل دخول، خليه null
        $order->product_id = $product->id;
        $order->product_name = $product->name;
        $order->price = 0;
        $order->name = $request->name;
        $order->email = $request->email;
        $order->phone = $request->phone;
        $order->order_status = 'تم الدفع'; // مجاني = مدفوع فورًا

        if (!$isDigital) {
            $order->country = $request->country;
            $order->city = $request->city;
            $order->address = $request->address;
        }

        $order->save();

        // لو رقمي → ابعت إيميل + خليه يحمل فورًا
        if ($isDigital && $product->digital_file) {
            $purchasesLink = route('user.products.purches');


            // Send Activation Email To User
            $email = $order['email'];
            $MessageDate = [
                'name' => $order['name'],
                "email" => $order['email'],
                'purchasesPageLink' => $purchasesLink,
                'productName' => $product->name,
            ];
            Mail::send('website.emails.DigitalProductMail', $MessageDate, function ($message) use ($email) {
                $message->to($email)->subject(' شراء المنتج الرقمي من نفذها   ');
            });

            // لو اليوزر مسجل دخول → نرحّب بيه ونقوله روح حمل
            if (Auth::check()) {
                DB::commit();
                return redirect()->route('user.products.purches')
                    ->with('success', 'تم إضافة المنتج المجاني لحسابك بنجاح! يمكنك تحميله الآن.');
            }
        }

        DB::commit();

        // لو مش مسجل دخول أو منتج فيزيكال مجاني → نرحّب بيه
        return redirect()->route('user.products.purches')
            ->with('success', 'تم تسجيل طلبك المجاني بنجاح! تم إرسال التفاصيل على الإيميل.');
    }
}
