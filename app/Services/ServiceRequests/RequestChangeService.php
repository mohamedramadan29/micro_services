<?php

namespace App\Services\ServiceRequests;

use App\Models\OnlinePayment;
use App\Models\RequestPayment;
use App\Models\ServiceRequest;
use App\Models\ServiceRequestChange;
use App\Models\ServiceRequestEvent;
use App\Models\User;
use App\Models\WalletTransaction;
use App\Notifications\RequestUpdateNotification;
use App\Support\Msg;
use App\Services\Payments\OnlinePaymentService;
use App\Services\Payments\RequestPaymentService;
use App\Support\Money;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Changes after work started (client decision 2026-10-16, «like Upwork»): the
 * client proposes, the chosen freelancer approves or rejects. A higher price is
 * applied once the client pays the difference (site balance or EasyKash); a
 * lower price gives the difference back to the client's site balance at once,
 * and the commission follows the new amount.
 */
class RequestChangeService
{
    public function __construct(private OnlinePaymentService $online)
    {
    }

    public function propose(ServiceRequest $request, User $client, array $data): ServiceRequestChange
    {
        return DB::transaction(function () use ($request, $client, $data) {
            $request = ServiceRequest::whereKey($request->id)->lockForUpdate()->first();
            if ((int) $request->user_id !== (int) $client->id || ! $request->isInProgress()) {
                throw new RuntimeException('طلبات التعديل متاحة بعد بدء التنفيذ فقط');
            }
            if ($request->openChange()) {
                throw new RuntimeException('لديك طلب تعديل لم يُحسم بعد. انتظر رد المستقل أو ألغِه أولًا.');
            }
            $oldPrice = $request->currentPrice();
            $newPrice = isset($data['new_price']) && $data['new_price'] !== null && $data['new_price'] !== '' ? round((float) $data['new_price'], 2) : $oldPrice;
            $newDeadline = $data['new_deadline'] ?? null;

            $change = $request->changes()->create([
                'requested_by' => $client->id,
                'freelancer_id' => $request->acceptedOffer?->freelancer_id,
                'description' => $data['description'],
                'reason' => $data['reason'] ?? null,
                'old_deadline' => $request->deadline,
                'new_deadline' => $newDeadline ?: $request->deadline,
                'old_price' => $oldPrice,
                'new_price' => $newPrice,
                'status' => ServiceRequestChange::STATUS_PENDING,
            ]);
            $this->log($change, 'أرسل العميل طلب تعديل' . $this->summary($change));
            DB::afterCommit(fn () => $change->freelancer?->notify(new RequestUpdateNotification(
                Msg::t('requests.change_proposed', ['number' => $request->number, 'changes' => $this->summary($change)]),
                '/client-requests/' . $request->number . '#changes',
                'request_changes'
            )));

            return $change;
        });
    }

    /** The chosen freelancer's answer. */
    public function respond(ServiceRequestChange $change, User $freelancer, bool $accept, ?string $note): ServiceRequestChange
    {
        return DB::transaction(function () use ($change, $freelancer, $accept, $note) {
            $change = ServiceRequestChange::whereKey($change->id)->lockForUpdate()->first();
            $request = ServiceRequest::whereKey($change->service_request_id)->lockForUpdate()->first();
            if ($change->status !== ServiceRequestChange::STATUS_PENDING || ! $request->isInProgress()
                || (int) $request->acceptedOffer?->freelancer_id !== (int) $freelancer->id) {
                throw new RuntimeException('لا يمكن الرد على طلب التعديل هذا');
            }

            $change->update(['responded_at' => now(), 'response_note' => $note]);
            if (! $accept) {
                $change->update(['status' => ServiceRequestChange::STATUS_REJECTED]);
                $this->log($change, 'رفض المستقل طلب التعديل' . ($note ? ': ' . $note : ''));
                $this->notifyClient($request, $note
                    ? Msg::t('requests.change_rejected_note', ['number' => $request->number, 'note' => $note])
                    : Msg::t('requests.change_rejected', ['number' => $request->number]));

                return $change;
            }

            if ($change->difference() > 0) {
                $change->update(['status' => ServiceRequestChange::STATUS_AWAITING_PAYMENT]);
                $this->log($change, 'وافق المستقل، بانتظار دفع الفرق ' . Money::format($change->difference()));
                $this->notifyClient($request, Msg::t('requests.change_awaiting_payment', ['number' => $request->number, 'amount' => Money::format($change->difference())]));

                return $change;
            }

            $this->apply($change, $request);

            return $change;
        });
    }

    /** The client withdraws a change the freelancer did not settle yet (an unpaid difference is cancelled). */
    public function cancel(ServiceRequestChange $change, User $client): void
    {
        DB::transaction(function () use ($change, $client) {
            $change = ServiceRequestChange::whereKey($change->id)->lockForUpdate()->first();
            if (! $change->isOpen() || (int) $change->requested_by !== (int) $client->id) {
                throw new RuntimeException('لا يمكن إلغاء طلب التعديل هذا');
            }
            $this->cancelPendingPayments($change);
            $change->update(['status' => ServiceRequestChange::STATUS_CANCELLED]);
            $this->log($change, 'ألغى العميل طلب التعديل');
        });
    }

    /** Pay the difference from the client's site balance. */
    public function payFromBalance(ServiceRequestChange $change, User $client): void
    {
        DB::transaction(function () use ($change, $client) {
            $change = ServiceRequestChange::whereKey($change->id)->lockForUpdate()->first();
            $request = ServiceRequest::whereKey($change->service_request_id)->lockForUpdate()->first();
            $this->assertPayable($change, $request, $client);
            $client = User::whereKey($client->id)->lockForUpdate()->first();
            $amount = $change->difference();
            if ((float) $client->balance + 0.001 < $amount) {
                throw new RuntimeException('رصيدك لا يكفي. ادفع الفرق عبر EasyKash أو اشحن رصيدك.');
            }

            $client->update(['balance' => round((float) $client->balance - $amount, 2)]);
            $this->cancelPendingPayments($change);
            $payment = $this->differencePayment($change, $request, 'balance');
            $this->hold($payment, $request, now(), null, 'من رصيدك');
            $this->apply($change, $request);
        });
    }

    /** Pay the difference through EasyKash; the change is applied when EasyKash confirms. */
    public function startCheckout(ServiceRequestChange $change, User $client): string
    {
        $online = DB::transaction(function () use ($change, $client) {
            $change = ServiceRequestChange::whereKey($change->id)->lockForUpdate()->first();
            $request = ServiceRequest::whereKey($change->service_request_id)->lockForUpdate()->first();
            $this->assertPayable($change, $request, $client);
            $this->cancelPendingPayments($change);
            $payment = $this->differencePayment($change, $request, 'easykash');
            $online = $this->online->create(OnlinePayment::PURPOSE_REQUEST, $client, $change->difference(),
                'فرق تعديل الطلب ' . $request->number, ['request_payment_id' => $payment->id],
                ['name' => $request->contact_name, 'email' => $request->contact_email, 'mobile' => $request->contact_phone]);
            $payment->update(['online_payment_id' => $online->id, 'gateway_reference' => (string) $online->id]);

            return $online;
        });

        return $this->online->checkout($online);
    }

    /**
     * EasyKash confirmed a difference payment (called by RequestPaymentService::paid inside its
     * transaction). Returns false when the change can no longer be applied: the money then
     * needs a refund like a duplicate payment.
     */
    public function differencePaid(RequestPayment $payment, OnlinePayment $online): bool
    {
        $change = ServiceRequestChange::whereKey($payment->service_request_change_id)->lockForUpdate()->first();
        $request = ServiceRequest::whereKey($payment->service_request_id)->lockForUpdate()->first();
        WalletTransaction::create([
            'user_id' => $payment->client_id,
            'request_payment_id' => $payment->id,
            'type' => WalletTransaction::TYPE_ESCROW_IN,
            'amount' => $payment->amount,
            'description' => 'فرق تعديل الطلب ' . $request->number . ' (محجوز لدى نفّذها)',
        ]);
        if (! $change || $change->status !== ServiceRequestChange::STATUS_AWAITING_PAYMENT || ! $request->isInProgress()) {
            $payment->update(['status' => RequestPayment::STATUS_CONFLICT, 'paid_at' => now(), 'gateway_transaction' => $online->gateway_transaction]);

            return false;
        }

        $this->hold($payment, $request, now(), $online->gateway_transaction, null);
        $this->apply($change, $request);

        return true;
    }

    /** Make the approved change the request's terms (inside the caller's transaction, rows locked). */
    private function apply(ServiceRequestChange $change, ServiceRequest $request): void
    {
        $difference = $change->difference();
        if ($difference < 0) {
            $this->giveBack($request, -$difference);
        }

        $request->update([
            'agreed_price' => $change->new_price,
            'deadline' => $change->new_deadline ?? $request->deadline,
        ]);
        $change->update(['status' => ServiceRequestChange::STATUS_APPLIED, 'applied_at' => now()]);
        $this->log($change, 'اعتُمد التعديل' . $this->summary($change) . ($difference < 0 ? ' — أُعيد ' . Money::format(-$difference) . ' إلى رصيد العميل' : ''));

        $text = $difference < 0
            ? Msg::t('requests.change_applied_refund', ['number' => $request->number, 'changes' => $this->summary($change), 'amount' => Money::format(-$difference)])
            : Msg::t('requests.change_applied', ['number' => $request->number, 'changes' => $this->summary($change)]);
        $this->notifyClient($request, $text);
        DB::afterCommit(fn () => $change->freelancer?->notify(new RequestUpdateNotification(
            Msg::t('requests.change_applied_freelancer', ['number' => $request->number, 'changes' => $this->summary($change)]),
            '/client-requests/' . $request->number . '#changes',
            'request_changes'
        )));
    }

    /** A lower price: the difference leaves the held money (newest payments first) for the client's balance. */
    private function giveBack(ServiceRequest $request, float $amount): void
    {
        $left = $amount;
        $payments = $request->payments()->where('status', RequestPayment::STATUS_PAID)->orderByDesc('id')->lockForUpdate()->get();
        foreach ($payments as $payment) {
            if ($left <= 0) {
                break;
            }
            $take = min($left, $payment->netAmount());
            if ($take <= 0) {
                continue;
            }
            $net = round($payment->netAmount() - $take, 2);
            $commission = round($net * (float) $payment->commission_rate / 100, 2);
            $payment->update([
                'refunded_amount' => round((float) $payment->refunded_amount + $take, 2),
                'commission_amount' => $commission,
                'freelancer_amount' => round($net - $commission, 2),
            ]);
            $left = round($left - $take, 2);
        }
        if ($left > 0.001) {
            throw new RuntimeException('المبلغ المحجوز لا يكفي لهذا التخفيض');
        }

        $client = User::whereKey($request->user_id)->lockForUpdate()->first();
        $client->update(['balance' => round((float) $client->balance + $amount, 2)]);
        WalletTransaction::create([
            'user_id' => $client->id,
            'request_payment_id' => $payments->first()?->id,
            'type' => WalletTransaction::TYPE_REFUND,
            'amount' => $amount,
            'description' => 'فرق تخفيض سعر الطلب ' . $request->number . ' أُعيد إلى رصيدك',
        ]);
    }

    private function assertPayable(ServiceRequestChange $change, ServiceRequest $request, User $client): void
    {
        if ($change->status !== ServiceRequestChange::STATUS_AWAITING_PAYMENT || (int) $change->requested_by !== (int) $client->id || ! $request->isInProgress()) {
            throw new RuntimeException('لا يوجد فرق مطلوب دفعه لهذا التعديل');
        }
    }

    private function differencePayment(ServiceRequestChange $change, ServiceRequest $request, string $gateway): RequestPayment
    {
        return $request->payments()->create([
            'service_request_change_id' => $change->id,
            'request_offer_id' => $request->accepted_offer_id,
            'client_id' => $request->user_id,
            'freelancer_id' => $change->freelancer_id,
            'amount' => $change->difference(),
            'currency' => strtoupper(config('services.easykash.currency', 'EGP')),
            'status' => RequestPayment::STATUS_PENDING,
            'gateway' => $gateway,
        ]);
    }

    /** Hold a paid difference like the first payment: commission at today's rate. */
    private function hold(RequestPayment $payment, ServiceRequest $request, $paidAt, ?string $transaction, ?string $source): void
    {
        $rate = RequestPaymentService::commissionRate();
        $commission = round((float) $payment->amount * $rate / 100, 2);
        $payment->update([
            'status' => RequestPayment::STATUS_PAID,
            'paid_at' => $paidAt,
            'gateway_transaction' => $transaction,
            'commission_rate' => $rate,
            'commission_amount' => $commission,
            'freelancer_amount' => round((float) $payment->amount - $commission, 2),
        ]);
        if ($source) {
            WalletTransaction::create([
                'user_id' => $payment->client_id,
                'request_payment_id' => $payment->id,
                'type' => WalletTransaction::TYPE_ESCROW_IN,
                'amount' => $payment->amount,
                'description' => 'فرق تعديل الطلب ' . $request->number . ' ' . $source . ' (محجوز لدى نفّذها)',
            ]);
        }
    }

    private function cancelPendingPayments(ServiceRequestChange $change): void
    {
        $pending = RequestPayment::where('service_request_change_id', $change->id)->where('status', RequestPayment::STATUS_PENDING);
        OnlinePayment::whereIn('id', (clone $pending)->whereNotNull('online_payment_id')->select('online_payment_id'))
            ->where('status', OnlinePayment::STATUS_PENDING)->update(['status' => OnlinePayment::STATUS_CANCELLED]);
        $pending->update(['status' => RequestPayment::STATUS_CANCELLED]);
    }

    private function summary(ServiceRequestChange $change): string
    {
        $parts = [];
        if ($change->difference() != 0) {
            $parts[] = 'السعر ' . Money::format($change->old_price) . ' ← ' . Money::format($change->new_price);
        }
        if ($change->new_deadline && $change->new_deadline?->toDateString() !== $change->old_deadline?->toDateString()) {
            $parts[] = 'الموعد ' . $change->new_deadline->format('Y-m-d');
        }

        return $parts ? ' (' . implode('، ', $parts) . ')' : '';
    }

    private function log(ServiceRequestChange $change, string $note): void
    {
        ServiceRequestEvent::record($change->request, ServiceRequestEvent::TYPE_CHANGE, ['note' => $note]);
    }

    private function notifyClient(ServiceRequest $request, string $text): void
    {
        DB::afterCommit(fn () => $request->user?->notify(new RequestUpdateNotification($text, '/my/requests/' . $request->number . '#changes', 'request_changes')));
    }
}
