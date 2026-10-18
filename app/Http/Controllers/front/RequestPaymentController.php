<?php

namespace App\Http\Controllers\front;

use App\Http\Controllers\Controller;
use App\Models\RequestOffer;
use App\Models\ServiceRequest;
use App\Services\Payments\RequestPaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class RequestPaymentController extends Controller
{
    public function __construct(private RequestPaymentService $payments)
    {
    }

    /** The client picks an offer: go to the hosted checkout to pay its price. */
    public function accept(RequestOffer $offer)
    {
        abort_unless((int) Auth::id() === (int) $offer->request->user_id, 403);

        // Service 3: a staged project is not paid at once; the contract and its agreement come first.
        if ($offer->request->isStaged()) {
            try {
                app(\App\Services\Contracts\StagedProjectService::class)->chooseOffer($offer);
            } catch (RuntimeException $e) {
                return back()->withErrors($e->getMessage());
            }

            return redirect()->route('requests.show', $offer->request)
                ->with('Success_message', 'اخترت ' . $offer->freelancer->name . '. الخطوة التالية: يقترح المستقل الاتفاق ومراحل الدفع والتسليم، ثم توافق عليه وتموّل المرحلة الأولى.');
        }

        try {
            return redirect()->away($this->payments->startCheckout($offer));
        } catch (RuntimeException $e) {
            Log::warning('Request checkout failed', ['offer' => $offer->id, 'error' => $e->getMessage()]);

            return back()->withErrors($e->getMessage());
        } catch (\Throwable $e) {
            Log::error('Request checkout failed', ['offer' => $offer->id, 'error' => $e->getMessage()]);

            return back()->withErrors('تعذّر بدء الدفع الآن، حاول مرة أخرى بعد قليل');
        }
    }

    /** The chosen freelancer delivers the work. */
    public function deliver(Request $request, ServiceRequest $serviceRequest)
    {
        $data = $request->validate(['delivery_note' => 'nullable|string|max:3000'], ['delivery_note.max' => 'الملاحظة يجب ألا تتجاوز 3000 حرف']);

        try {
            $this->payments->deliver($serviceRequest, Auth::user(), $data['delivery_note'] ?? null);
        } catch (RuntimeException $e) {
            abort(403, $e->getMessage());
        }

        return back()->with('Success_message', 'تم إبلاغ العميل بتسليم العمل، وسيُحوّل المبلغ لرصيدك بعد تأكيده الاستلام.');
    }

    /** The client confirms the work: the freelancer gets paid. */
    public function complete(ServiceRequest $serviceRequest)
    {
        abort_unless((int) Auth::id() === (int) $serviceRequest->user_id, 403);

        try {
            $this->payments->release($serviceRequest);
        } catch (RuntimeException $e) {
            return back()->withErrors($e->getMessage());
        }

        return back()->with('Success_message', 'شكرًا لك! تم تأكيد الاستلام وتحويل المستحقات للمستقل.');
    }
}
