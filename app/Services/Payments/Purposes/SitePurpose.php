<?php

namespace App\Services\Payments\Purposes;

use App\Models\OnlinePayment;
use App\Services\Payments\PaymentPurpose;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Shared behaviour of the direct purchases (service, product, package,
 * course, balance top-up): nothing to undo on failure, and the payer is sent
 * to a "done" page or back to where they started.
 */
abstract class SitePurpose implements PaymentPurpose
{
    /** Page shown once paid (or while a cash payment is pending). */
    abstract protected function doneUrl(OnlinePayment $payment): string;

    abstract protected function paidMessage(): string;

    public function failed(OnlinePayment $payment): void
    {
    }

    public function response(OnlinePayment $payment): RedirectResponse
    {
        return match ($payment->status) {
            OnlinePayment::STATUS_PAID => redirect()->to($this->doneUrl($payment))->with('Success_message', $this->paidMessage()),
            OnlinePayment::STATUS_PENDING => redirect()->to($payment->data('back_url') ?: $this->doneUrl($payment))
                ->with('Success_message', 'بانتظار إتمام الدفع (مثل فوري أو المحفظة). بمجرد الدفع سيتم تفعيل طلبك تلقائيًا.'),
            default => redirect()->to($payment->data('back_url') ?: url('/'))->withErrors('لم يكتمل الدفع. يمكنك المحاولة مرة أخرى.'),
        };
    }

    /**
     * Emails and notifications go out only after the payment is committed, and
     * never undo it when the mail server fails.
     */
    protected function afterCommit(callable $callback): void
    {
        DB::afterCommit(function () use ($callback) {
            try {
                $callback();
            } catch (\Throwable $e) {
                Log::warning('Payment follow-up failed', ['error' => $e->getMessage()]);
            }
        });
    }
}
