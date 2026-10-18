<?php

namespace App\Services\Payments;

use App\Models\admin\Setting;
use App\Models\OnlinePayment;
use App\Models\RequestOffer;
use App\Models\RequestPayment;
use App\Models\ServiceRequest;
use App\Models\User;
use App\Models\WalletTransaction;
use App\Notifications\OfferNotSelectedNotification;
use App\Notifications\OfferSelectedNotification;
use App\Notifications\PaymentConflictNotification;
use App\Notifications\PaymentReleasedNotification;
use App\Notifications\WorkDeliveredNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use RuntimeException;

/**
 * Money flow of a service request:
 *   client picks an offer → pays through the gateway (held by the site)
 *   → freelancer delivers → client confirms → freelancer is paid minus the
 *   site commission (rate fixed at payment time).
 */
class RequestPaymentService implements PaymentPurpose
{
    /** Used when the admin never set a commission. */
    public const DEFAULT_COMMISSION = 10;

    public function __construct(private PaymentGateway $gateway, private OnlinePaymentService $online)
    {
    }

    /** Create a pending payment for the offer and return the checkout URL. */
    public function startCheckout(RequestOffer $offer): string
    {
        $request = $offer->request;
        if (! $request->isOpen() || ! $offer->isActive()) {
            throw new RuntimeException('لا يمكن اختيار هذا العرض الآن');
        }

        // Only one live checkout per request (a replaced one is still settled if it gets paid).
        $pending = $request->payments()->where('status', RequestPayment::STATUS_PENDING);
        OnlinePayment::whereIn('id', (clone $pending)->whereNotNull('online_payment_id')->select('online_payment_id'))
            ->where('status', OnlinePayment::STATUS_PENDING)->update(['status' => OnlinePayment::STATUS_CANCELLED]);
        $pending->update(['status' => RequestPayment::STATUS_CANCELLED]);

        $payment = $request->payments()->create([
            'request_offer_id' => $offer->id,
            'client_id' => $request->user_id,
            'freelancer_id' => $offer->freelancer_id,
            'amount' => $offer->price,
            'currency' => strtoupper(config('services.easykash.currency', 'EGP')),
            'status' => RequestPayment::STATUS_PENDING,
            'gateway' => 'easykash',
        ]);
        $online = $this->online->create(OnlinePayment::PURPOSE_REQUEST, $request->user, (float) $offer->price, 'دفع قيمة الطلب ' . $request->number,
            ['request_payment_id' => $payment->id],
            ['name' => $request->contact_name, 'email' => $request->contact_email, 'mobile' => $request->contact_phone]);
        $payment->update(['online_payment_id' => $online->id, 'gateway_reference' => (string) $online->id]);

        return $this->online->checkout($online);
    }

    /** Apply a gateway report about a request payment (see OnlinePaymentService::apply). */
    public function fulfill(CheckoutSession $session): ?RequestPayment
    {
        $online = $this->online->apply($session);

        return $online ? RequestPayment::where('online_payment_id', $online->id)->first() : null;
    }

    public function failed(OnlinePayment $online): void
    {
        RequestPayment::where('online_payment_id', $online->id)->where('status', RequestPayment::STATUS_PENDING)
            ->update(['status' => RequestPayment::STATUS_CANCELLED]);
    }

    public function response(OnlinePayment $online): RedirectResponse
    {
        $payment = RequestPayment::where('online_payment_id', $online->id)->firstOrFail();
        $redirect = redirect()->route('requests.show', $payment->request);
        if ($payment->service_request_change_id) {
            return match ($payment->status) {
                RequestPayment::STATUS_PAID => $redirect->with('Success_message', 'تم دفع الفرق واعتُمد تعديل الطلب.'),
                RequestPayment::STATUS_CONFLICT => $redirect->withErrors('استلمنا الدفعة لكن طلب التعديل لم يعد قائمًا. سيتواصل معك فريقنا لاسترجاع المبلغ.'),
                RequestPayment::STATUS_PENDING => $redirect->with('Success_message', 'بانتظار إتمام دفع الفرق. يُعتمد التعديل تلقائيًا بمجرد الدفع.'),
                default => $redirect->withErrors('لم يكتمل دفع الفرق، ولم يُعتمد التعديل بعد. يمكنك المحاولة مرة أخرى.'),
            };
        }

        return match ($payment->status) {
            RequestPayment::STATUS_PAID, RequestPayment::STATUS_RELEASED => $redirect->with('Success_message', 'تم الدفع بنجاح. المبلغ محجوز لدى نفّذها ولن يُحوّل للمستقل إلا بعد تأكيدك استلام العمل.'),
            RequestPayment::STATUS_CONFLICT => $redirect->withErrors('استلمنا دفعتك، لكن طلبك كان قد أُسند لعرض آخر. سيتواصل معك فريقنا لاسترجاع المبلغ.'),
            RequestPayment::STATUS_PENDING => $redirect->with('Success_message', 'طلبك بانتظار إتمام الدفع (مثل فوري أو المحفظة). بمجرد الدفع سيُحدَّث طلبك تلقائيًا ويصلك إشعار.'),
            default => $redirect->withErrors('لم يكتمل الدفع، ولم يُختر أي عرض. يمكنك المحاولة مرة أخرى.'),
        };
    }

    /**
     * EasyKash confirmed the payment (runs once, inside OnlinePaymentService's
     * transaction): hold the money and give the request to the offer.
     */
    public function paid(OnlinePayment $online): void
    {
        $payment = RequestPayment::where('online_payment_id', $online->id)->first();
        if (! $payment) {
            Log::error('Paid online payment without a request payment', ['online_payment' => $online->id]);

            return;
        }

        $outcome = DB::transaction(function () use ($payment, $online) {
            $payment = RequestPayment::whereKey($payment->id)->lockForUpdate()->first();
            if (in_array($payment->status, [RequestPayment::STATUS_PAID, RequestPayment::STATUS_RELEASED, RequestPayment::STATUS_REFUNDED, RequestPayment::STATUS_CONFLICT], true)) {
                return ['payment' => $payment, 'fresh' => false];
            }

            // A price difference after an approved change (RequestChangeService).
            if ($payment->service_request_change_id) {
                $applied = app(\App\Services\ServiceRequests\RequestChangeService::class)->differencePaid($payment, $online);

                return ['payment' => $payment->fresh(), 'fresh' => false, 'conflict' => ! $applied];
            }

            $request = ServiceRequest::whereKey($payment->service_request_id)->lockForUpdate()->first();
            $offer = RequestOffer::whereKey($payment->request_offer_id)->lockForUpdate()->first();
            $common = ['paid_at' => now(), 'gateway_transaction' => $online->gateway_transaction];

            WalletTransaction::create([
                'user_id' => $payment->client_id,
                'request_payment_id' => $payment->id,
                'type' => WalletTransaction::TYPE_ESCROW_IN,
                'amount' => $payment->amount,
                'description' => 'دفع قيمة الطلب ' . $request->number . ' (محجوز لدى نفّذها)',
            ]);

            // Paid, but the request already went to another offer (e.g. two tabs): needs a refund.
            if (! $request->isOpen() || ! $offer || ! $offer->isActive()) {
                $payment->update($common + ['status' => RequestPayment::STATUS_CONFLICT]);

                return ['payment' => $payment, 'fresh' => false, 'conflict' => true];
            }

            $rate = self::commissionRate();
            $commission = round((float) $payment->amount * $rate / 100, 2);
            $payment->update($common + [
                'status' => RequestPayment::STATUS_PAID,
                'commission_rate' => $rate,
                'commission_amount' => $commission,
                'freelancer_amount' => round((float) $payment->amount - $commission, 2),
            ]);

            $offer->update(['status' => RequestOffer::STATUS_ACCEPTED, 'is_saved' => false]);
            $request->offers()->whereKeyNot($offer->id)->whereIn('status', RequestOffer::ACTIVE_STATUSES)
                ->update(['status' => RequestOffer::STATUS_REJECTED]);
            $request->update(['status' => ServiceRequest::STATUS_IN_PROGRESS, 'accepted_offer_id' => $offer->id, 'agreed_price' => $payment->amount]);

            return ['payment' => $payment, 'fresh' => true];
        });

        // Sent after apply()'s transaction commits: a mail/template error never undoes a paid mark.
        DB::afterCommit(function () use ($outcome) {
            if ($outcome['fresh']) {
                $this->notifySelection($outcome['payment']);
            }
            if ($outcome['conflict'] ?? false) {
                Notification::send(User::activeAdmins()->get(), new PaymentConflictNotification($outcome['payment']));
            }
        });
    }

    /** The freelancer says the work is done (the client still has to confirm). */
    public function deliver(ServiceRequest $request, User $freelancer, ?string $note): void
    {
        if ($request->status !== ServiceRequest::STATUS_IN_PROGRESS || (int) $request->acceptedOffer?->freelancer_id !== (int) $freelancer->id) {
            throw new RuntimeException('لا يمكن تسليم هذا الطلب');
        }

        $request->update(['delivered_at' => now(), 'delivery_note' => $note]);
        $request->user->notify(new WorkDeliveredNotification($request));
    }

    /**
     * The client confirms the work: pay the freelancer and book the commission, for the first
     * payment and every paid price difference (amounts are already net of any price decrease).
     */
    public function release(ServiceRequest $request): RequestPayment
    {
        [$payment, $total] = DB::transaction(function () use ($request) {
            $request = ServiceRequest::whereKey($request->id)->lockForUpdate()->first();
            $payments = $request->payments()->where('status', RequestPayment::STATUS_PAID)->orderBy('id')->lockForUpdate()->get();
            if ($request->status !== ServiceRequest::STATUS_IN_PROGRESS || $payments->isEmpty()) {
                throw new RuntimeException('لا يوجد مبلغ محجوز لهذا الطلب');
            }

            // An unfinished change request ends with the work.
            foreach ($request->changes()->whereIn('status', \App\Models\ServiceRequestChange::OPEN_STATUSES)->get() as $open) {
                RequestPayment::where('service_request_change_id', $open->id)->where('status', RequestPayment::STATUS_PENDING)
                    ->update(['status' => RequestPayment::STATUS_CANCELLED]);
                $open->update(['status' => \App\Models\ServiceRequestChange::STATUS_CANCELLED]);
            }

            $freelancer = User::whereKey($payments->first()->freelancer_id)->lockForUpdate()->first();
            $setting = Setting::query()->lockForUpdate()->first() ?? Setting::create(['website_commission' => (string) self::DEFAULT_COMMISSION]);
            $total = 0.0;
            foreach ($payments as $payment) {
                $freelancer->update(['balance' => round((float) $freelancer->balance + (float) $payment->freelancer_amount, 2)]);
                $setting->update(['website_balance' => round((float) $setting->website_balance + (float) $payment->commission_amount, 2)]);
                $label = $payment->service_request_change_id ? 'فرق تعديل الطلب ' : 'مستحقات الطلب ';

                WalletTransaction::create([
                    'user_id' => $freelancer->id,
                    'request_payment_id' => $payment->id,
                    'type' => WalletTransaction::TYPE_RELEASE,
                    'amount' => $payment->freelancer_amount,
                    'description' => $label . $request->number . ' بعد خصم عمولة ' . (float) $payment->commission_rate . '%',
                ]);
                WalletTransaction::create([
                    'user_id' => null,
                    'request_payment_id' => $payment->id,
                    'type' => WalletTransaction::TYPE_COMMISSION,
                    'amount' => $payment->commission_amount,
                    'description' => 'عمولة الموقع من الطلب ' . $request->number,
                ]);

                $payment->update(['status' => RequestPayment::STATUS_RELEASED, 'released_at' => now()]);
                $total += (float) $payment->freelancer_amount;
            }
            $request->update(['status' => ServiceRequest::STATUS_COMPLETED, 'completed_at' => now()]);

            return [$payments->first(), round($total, 2)];
        });

        $payment->freelancer->notify(new PaymentReleasedNotification($payment, $total));

        return $payment;
    }

    /**
     * Give a held (or duplicate) payment back to the client: what is left of it after any
     * price decrease. Paid from the site balance → back to the balance; else EasyKash.
     */
    public function refund(RequestPayment $payment): RequestPayment
    {
        if (! in_array($payment->status, [RequestPayment::STATUS_PAID, RequestPayment::STATUS_CONFLICT], true)) {
            throw new RuntimeException('لا يمكن استرجاع هذه الدفعة (' . $payment->statusLabel() . ')');
        }
        $toBalance = $payment->gateway === 'balance';

        // The gateway call happens first: if it fails, nothing changes here. A
        // null reference means the admin refunded from the gateway dashboard.
        $refundId = $toBalance ? 'balance' : (($payment->onlinePayment ? $this->gateway->refund($payment->onlinePayment) : null) ?? 'manual');

        return DB::transaction(function () use ($payment, $refundId, $toBalance) {
            $payment = RequestPayment::whereKey($payment->id)->lockForUpdate()->first();
            if ($payment->status === RequestPayment::STATUS_REFUNDED) {
                return $payment;
            }
            $amount = $payment->netAmount();
            $payment->update(['status' => RequestPayment::STATUS_REFUNDED, 'refund_reference' => $refundId]);
            if ($toBalance) {
                $client = User::whereKey($payment->client_id)->lockForUpdate()->first();
                $client->update(['balance' => round((float) $client->balance + $amount, 2)]);
            }
            WalletTransaction::create([
                'user_id' => $payment->client_id,
                'request_payment_id' => $payment->id,
                'type' => WalletTransaction::TYPE_REFUND,
                'amount' => $amount,
                'description' => 'استرجاع مبلغ الطلب ' . $payment->request->number . match ($refundId) {
                    'balance' => ' (إلى رصيدك في الموقع)',
                    'manual' => ' (من لوحة EasyKash)',
                    default => ' (' . $refundId . ')',
                },
            ]);

            return $payment;
        });
    }

    /** Site commission percentage for every sale (admin setting, default 10%). */
    public static function commissionRate(): float
    {
        $value = Setting::first()?->website_commission;

        return is_numeric($value) ? max(0, min(100, (float) $value)) : self::DEFAULT_COMMISSION;
    }

    private function notifySelection(RequestPayment $payment): void
    {
        $payment->loadMissing(['offer.freelancer', 'request.offers.freelancer']);
        $payment->offer->freelancer?->notify(new OfferSelectedNotification($payment->offer));
        foreach ($payment->request->offers->where('status', RequestOffer::STATUS_REJECTED) as $other) {
            $other->freelancer?->notify(new OfferNotSelectedNotification($other));
        }
    }
}
