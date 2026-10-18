<?php

namespace App\Http\Controllers\front;

use App\Http\Controllers\Controller;
use App\Models\OnlinePayment;
use App\Services\Payments\OnlinePaymentService;
use App\Services\Payments\PaymentGateway;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/** EasyKash return page and webhook for every payment on the site. */
class OnlinePaymentController extends Controller
{
    public function __construct(private OnlinePaymentService $payments, private PaymentGateway $gateway)
    {
    }

    /**
     * Back from EasyKash. The query string (status=PAID…) is never trusted:
     * the payment is checked with EasyKash itself. Cash methods (Fawry,
     * wallets) can still be pending; the webhook or `payments:sync` finishes them.
     */
    public function return(OnlinePayment $onlinePayment, string $token)
    {
        abort_unless(hash_equals($onlinePayment->return_token, $token), 404);

        try {
            $onlinePayment = $this->payments->sync($onlinePayment);
        } catch (\Throwable $e) {
            Log::warning('Payment check failed', ['payment' => $onlinePayment->id, 'error' => $e->getMessage()]);
        }

        $response = $this->payments->purpose($onlinePayment)->response($onlinePayment);

        // An unfinished payment: optionally ask why (journey analytics, only for consenting visitors).
        if (in_array($onlinePayment->status, [OnlinePayment::STATUS_FAILED, OnlinePayment::STATUS_CANCELLED], true) && $onlinePayment->visit_id) {
            $response->with('abandon_payment', $onlinePayment->id);
        }

        return $response;
    }

    /**
     * EasyKash server-to-server notification. Anyone can call this URL, so the
     * payload is only a trigger: the payment is checked with EasyKash itself
     * (sync does nothing for payments that are already settled).
     */
    public function webhook(Request $request)
    {
        try {
            $session = $this->gateway->parseWebhook($request->all());
        } catch (\Throwable $e) {
            Log::warning('EasyKash webhook rejected', ['error' => $e->getMessage()]);

            return response()->json(['error' => 'invalid'], 401);
        }

        $payment = $session && ctype_digit($session->reference) ? OnlinePayment::find((int) $session->reference) : null;
        if ($payment) {
            try {
                $this->payments->sync($payment);
            } catch (\Throwable $e) {
                // EasyKash unreachable or amount refused: payments:sync retries later.
                Log::warning('EasyKash webhook: payment check failed', ['payment' => $payment->id, 'error' => $e->getMessage()]);

                return response()->json(['error' => 'retry later'], 503);
            }
        }

        return response()->json(['status' => 'ok']);
    }
}
