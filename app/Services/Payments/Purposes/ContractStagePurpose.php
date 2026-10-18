<?php

namespace App\Services\Payments\Purposes;

use App\Models\Contract;
use App\Models\OnlinePayment;
use App\Services\Contracts\ContractLedger;
use App\Services\Payments\PaymentPurpose;
use Illuminate\Http\RedirectResponse;

/** Funding contract stages through EasyKash (service 3). */
class ContractStagePurpose implements PaymentPurpose
{
    public function __construct(private ContractLedger $ledger)
    {
    }

    public function paid(OnlinePayment $payment): void
    {
        $this->ledger->fundingPaid($payment);
        // A funded current stage starts now (its deadline counts from here).
        if ($contract = Contract::find($payment->payload['contract_id'] ?? null)) {
            app(\App\Services\Contracts\StageWorkflow::class)->advance($contract);
        }
    }

    public function failed(OnlinePayment $payment): void
    {
        $this->ledger->fundingFailed($payment);
    }

    public function response(OnlinePayment $payment): RedirectResponse
    {
        $contract = Contract::find($payment->payload['contract_id'] ?? null);
        $redirect = $contract ? redirect()->route('contracts.show', $contract) : redirect()->route('user_balance');

        return match ($payment->status) {
            OnlinePayment::STATUS_PAID => $redirect->with('Success_message', 'تم تمويل المراحل. يبقى المبلغ لدى نفّذها حتى تعتمد المرحلة.'),
            OnlinePayment::STATUS_PENDING => $redirect->with('Success_message', 'بانتظار إتمام الدفع (مثل فوري أو المحفظة). تُموَّل المراحل تلقائيًا بمجرد الدفع.'),
            default => $redirect->withErrors('لم يكتمل الدفع، ولم تُموَّل المراحل. يمكنك المحاولة مرة أخرى.'),
        };
    }
}
