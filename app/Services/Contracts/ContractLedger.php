<?php

namespace App\Services\Contracts;

use App\Models\admin\Setting;
use App\Models\Contract;
use App\Models\ContractStage;
use App\Models\OnlinePayment;
use App\Models\User;
use App\Models\WalletTransaction;
use App\Services\Payments\OnlinePaymentService;
use App\Services\Payments\RequestPaymentService;
use App\Support\Money;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * The money of service 3 (client decisions 2026-10-15). EasyKash only collects money, so
 * the site keeps the books: a funded stage stays with Nafizha until it is approved.
 *
 * - Funding: from the client's site balance, or EasyKash with its fees added on top (paid by
 *   the client, never refunded); several stages may be funded at once.
 * - Release: the commission (rate fixed when funded) comes out of the freelancer's share.
 * - Refund: the full stage amount back to the client's site balance, no commission.
 * - Split: commission only on the freelancer's part, the rest to the client's balance.
 * - A stage is released to the contract's freelancer at that moment, so after a change of
 *   freelancer each one only pays commission on the stages released to them.
 */
class ContractLedger
{
    public function __construct(private OnlinePaymentService $online)
    {
    }

    /** EasyKash fees the client pays on top of an amount (admin settings). */
    public static function gatewayFee(float $amount): float
    {
        $setting = Setting::first();
        $fee = $amount * (float) ($setting?->stage_fee_percent ?? 0) / 100 + (float) ($setting?->stage_fee_fixed ?? 0);

        return round(max(0, $fee), 2);
    }

    /** Fund stages from the client's site balance (all or nothing). */
    public function fundFromBalance(Contract $contract, array $stageIds, User $client): Collection
    {
        return DB::transaction(function () use ($contract, $stageIds, $client) {
            $stages = $this->fundable($contract, $stageIds, $client);
            $total = round((float) $stages->sum('amount'), 2);
            $client = User::whereKey($client->id)->lockForUpdate()->first();
            if ((float) $client->balance + 0.001 < $total) {
                throw new RuntimeException('رصيدك ' . Money::format($client->balance) . ' لا يكفي لتمويل ' . Money::format($total) . '. اشحن رصيدك أو ادفع عبر EasyKash.');
            }
            $client->update(['balance' => round((float) $client->balance - $total, 2)]);
            foreach ($stages as $stage) {
                $this->hold($stage, 'balance', null);
            }
            $this->funded($contract, $stages, 'balance');

            return $stages;
        });
    }

    /** Fund stages through EasyKash (amount + fees); they are held once EasyKash confirms. */
    public function startFunding(Contract $contract, array $stageIds, User $client): string
    {
        $online = DB::transaction(function () use ($contract, $stageIds, $client) {
            $stages = $this->fundable($contract, $stageIds, $client);
            $total = round((float) $stages->sum('amount'), 2);
            $fee = self::gatewayFee($total);
            $online = $this->online->create(OnlinePayment::PURPOSE_CONTRACT_STAGE, $client, $total + $fee,
                'تمويل مراحل ' . $contract->number . ': ' . $stages->pluck('title')->implode('، '),
                ['contract_id' => $contract->id, 'stage_ids' => $stages->pluck('id')->all(), 'stages_total' => $total, 'fee' => $fee]);
            ContractStage::whereIn('id', $stages->pluck('id'))->update(['status' => ContractStage::STATUS_FUNDING, 'online_payment_id' => $online->id]);

            return $online;
        });

        return $this->online->checkout($online);
    }

    /**
     * EasyKash confirmed a funding payment (inside OnlinePaymentService's transaction). A stage
     * no longer waiting for this payment does not take the money: it goes to the client's
     * balance instead, so nothing is ever lost or held twice.
     */
    public function fundingPaid(OnlinePayment $online): void
    {
        $leftover = 0.0;
        $held = collect();
        foreach (ContractStage::whereIn('id', (array) ($online->payload['stage_ids'] ?? []))->lockForUpdate()->get() as $stage) {
            if ($stage->status === ContractStage::STATUS_FUNDING && (int) $stage->online_payment_id === (int) $online->id) {
                $this->hold($stage, 'easykash', $online);
                $held->push($stage);
            } else {
                $leftover += (float) $stage->amount;
            }
        }
        if ($leftover > 0) {
            $this->toClientBalance((int) $online->user_id, $leftover, null, 'مبلغ تمويل لمرحلة لم تعد بانتظار الدفع أُضيف إلى رصيدك (' . $online->description . ')');
        }
        if ($contract = Contract::find($online->payload['contract_id'] ?? null)) {
            if ($held->isNotEmpty()) {
                $this->funded($contract, $held, 'easykash');
            }
            if ($leftover > 0) {
                $this->notify($contract->client, \App\Support\Msg::t('contracts.funding_leftover', ['number' => $contract->number, 'amount' => Money::format($leftover)]), $contract);
            }
        }
    }

    /** The funding payment failed: its stages can be funded again. */
    public function fundingFailed(OnlinePayment $online): void
    {
        $reset = ContractStage::where('online_payment_id', $online->id)->where('status', ContractStage::STATUS_FUNDING)
            ->update(['status' => ContractStage::STATUS_UNFUNDED, 'online_payment_id' => null]);
        if ($reset && ($contract = Contract::find($online->payload['contract_id'] ?? null))) {
            $this->log($contract, 'لم يكتمل دفع تمويل المراحل عبر EasyKash (' . Money::format($online->amount) . ')');
            $this->notify($contract->client, \App\Support\Msg::t('contracts.funding_failed', ['number' => $contract->number]), $contract);
        }
    }

    /** An addendum lowers a funded stage: the difference goes back to the client's balance at once. */
    public function lowerHeld(ContractStage $stage, float $amount, string $why): void
    {
        $difference = round((float) $stage->amount - $amount, 2);
        if ($stage->status !== ContractStage::STATUS_FUNDED || $difference <= 0) {
            throw new RuntimeException('لا يمكن تخفيض مبلغ هذه المرحلة');
        }
        $stage->update(['amount' => $amount]);
        $this->toClientBalance((int) $stage->contract->client_id, $difference, $stage, 'فرق تخفيض المرحلة «' . $stage->title . '» (' . $why . ') أُعيد إلى رصيدك');
    }

    /** The client gave up on an EasyKash payment: the stage goes back to «غير مموّلة» (a late payment would still land in the balance). */
    public function cancelFunding(ContractStage $stage): void
    {
        DB::transaction(function () use ($stage) {
            $stage = ContractStage::whereKey($stage->id)->lockForUpdate()->first();
            if ($stage->status !== ContractStage::STATUS_FUNDING || ! $stage->online_payment_id) {
                throw new RuntimeException('لا يوجد دفع معلّق على هذه المرحلة');
            }
            $online = OnlinePayment::whereKey($stage->online_payment_id)->lockForUpdate()->first();
            if ($online && $online->status === OnlinePayment::STATUS_PENDING) {
                $online->update(['status' => OnlinePayment::STATUS_CANCELLED]);
            }
            ContractStage::where('online_payment_id', $stage->online_payment_id)->where('status', ContractStage::STATUS_FUNDING)
                ->update(['status' => ContractStage::STATUS_UNFUNDED, 'online_payment_id' => null]);
            $this->log($stage->contract, 'أُلغي الدفع المعلّق للمرحلة «' . $stage->title . '»');
        });
    }

    /** Funding confirmed (balance or EasyKash): logged on the request and told to both sides. */
    private function funded(Contract $contract, Collection $stages, string $source): void
    {
        $titles = $stages->map(fn ($s) => '«' . $s->title . '»')->implode('، ');
        $total = Money::format((float) $stages->sum('amount'));
        $this->log($contract, 'موّل العميل ' . ($stages->count() > 1 ? 'المراحل ' : 'المرحلة ') . $titles . ' بإجمالي ' . $total . ($source === 'balance' ? ' من رصيده' : ' عبر EasyKash'));
        $this->notify($contract->client, \App\Support\Msg::t('contracts.stages_funded_client', ['stages' => $titles, 'number' => $contract->number, 'total' => $total]), $contract);
        // The current stage tells the freelancer when it starts (StageWorkflow::advance); only stages funded ahead are announced here.
        $current = app(StageWorkflow::class)->currentStage($contract);
        $ahead = $stages->reject(fn ($s) => $current && (int) $s->id === (int) $current->id);
        if ($ahead->isNotEmpty()) {
            $this->notify($contract->freelancer, \App\Support\Msg::t('contracts.stages_funded_ahead', ['stages' => $ahead->map(fn ($s) => '«' . $s->title . '»')->implode('، '), 'number' => $contract->number]), $contract);
        }
    }

    private function log(Contract $contract, string $note): void
    {
        if ($contract->request) {
            \App\Models\ServiceRequestEvent::record($contract->request, \App\Models\ServiceRequestEvent::TYPE_AGREEMENT, ['note' => $note]);
        }
    }

    private function notify(?User $user, string $text, Contract $contract): void
    {
        DB::afterCommit(fn () => $user?->notify(new \App\Notifications\RequestUpdateNotification($text, '/contracts/' . $contract->number, 'contracts')));
    }

    /** The stage is approved: the contract's freelancer is paid minus the commission. */
    public function release(ContractStage $stage, ?string $note = null): ContractStage
    {
        return $this->settle($stage, (float) $stage->amount, ContractStage::STATUS_RELEASED, $note);
    }

    /** The full stage amount goes back to the client's site balance (no commission). */
    public function refund(ContractStage $stage, ?string $note = null): ContractStage
    {
        return $this->settle($stage, 0.0, ContractStage::STATUS_REFUNDED, $note);
    }

    /** Part to the freelancer (commission on that part only), the rest to the client's balance. */
    public function split(ContractStage $stage, float $freelancerPart, ?string $note = null): ContractStage
    {
        if ($freelancerPart <= 0 || $freelancerPart >= (float) $stage->amount) {
            throw new RuntimeException('حصة المستقل في التقسيم يجب أن تكون أكبر من صفر وأقل من مبلغ المرحلة');
        }

        return $this->settle($stage, round($freelancerPart, 2), ContractStage::STATUS_SPLIT, $note);
    }

    /** A new freelancer continues: funded stages not released yet will be released to them. */
    public function changeFreelancer(Contract $contract, User $freelancer): void
    {
        if (! $freelancer->isFreelancer()) {
            throw new RuntimeException('اختر حساب مستقل');
        }
        $contract->update(['freelancer_id' => $freelancer->id]);
    }

    private function settle(ContractStage $stage, float $freelancerPart, string $status, ?string $note): ContractStage
    {
        return DB::transaction(function () use ($stage, $freelancerPart, $status, $note) {
            $stage = ContractStage::whereKey($stage->id)->lockForUpdate()->first();
            $contract = Contract::whereKey($stage->contract_id)->lockForUpdate()->first();
            if ($stage->status !== ContractStage::STATUS_FUNDED) {
                throw new RuntimeException('هذه المرحلة ليست ممولة الآن (' . $stage->statusLabel() . ')');
            }
            if ($freelancerPart > 0 && ! $contract->freelancer_id) {
                throw new RuntimeException('لا يوجد مستقل على هذا العقد لتحويل المبلغ إليه');
            }

            $commission = round($freelancerPart * (float) $stage->commission_rate / 100, 2);
            $toFreelancer = round($freelancerPart - $commission, 2);
            $toClient = round((float) $stage->amount - $freelancerPart, 2);
            $label = 'المرحلة «' . $stage->title . '» من ' . $contract->number;

            if ($freelancerPart > 0) {
                $freelancer = User::whereKey($contract->freelancer_id)->lockForUpdate()->first();
                $freelancer->update(['balance' => round((float) $freelancer->balance + $toFreelancer, 2)]);
                $setting = Setting::query()->lockForUpdate()->first() ?? Setting::create(['website_commission' => (string) RequestPaymentService::DEFAULT_COMMISSION]);
                $setting->update(['website_balance' => round((float) $setting->website_balance + $commission, 2)]);
                $this->entry($freelancer->id, $stage, WalletTransaction::TYPE_RELEASE, $toFreelancer, 'مستحقات ' . $label . ' بعد خصم عمولة ' . (float) $stage->commission_rate . '%');
                $this->entry(null, $stage, WalletTransaction::TYPE_COMMISSION, $commission, 'عمولة الموقع من ' . $label);
            }
            if ($toClient > 0) {
                $this->toClientBalance((int) $contract->client_id, $toClient, $stage, ($status === ContractStage::STATUS_SPLIT ? 'الجزء المسترد من ' : 'استرجاع ') . $label . ' إلى رصيدك');
            }

            $stage->update([
                'status' => $status,
                'released_to' => $freelancerPart > 0 ? $contract->freelancer_id : null,
                'released_amount' => $freelancerPart,
                'commission_amount' => $commission,
                'freelancer_amount' => $toFreelancer,
                'refunded_amount' => $toClient,
                'decision_note' => $note,
                'settled_at' => now(),
            ]);
            if ($contract->stages()->whereIn('status', [ContractStage::STATUS_UNFUNDED, ContractStage::STATUS_FUNDING, ContractStage::STATUS_FUNDED])->doesntExist()) {
                // Completed only when the last stage was accepted (released or split); a refund ends the project without completing it.
                $contract->update($status === ContractStage::STATUS_REFUNDED
                    ? ['status' => Contract::STATUS_CANCELLED]
                    : ['status' => Contract::STATUS_COMPLETED, 'completed_at' => now()]);
            }

            return $stage;
        });
    }

    /** Lock and check the stages the client wants to fund. */
    private function fundable(Contract $contract, array $stageIds, User $client): Collection
    {
        $contract = Contract::whereKey($contract->id)->lockForUpdate()->first();
        if ((int) $contract->client_id !== (int) $client->id || $contract->status !== Contract::STATUS_ACTIVE) {
            throw new RuntimeException('لا يمكن تمويل مراحل هذا العقد');
        }
        if ($contract->freelancerChanges()->where('status', \App\Models\ContractFreelancerChange::STATUS_PENDING)->exists()) {
            throw new RuntimeException('يوجد طلب تغيير المستقل بانتظار قرار الإدارة؛ لا تُموَّل مراحل جديدة حتى القرار.');
        }
        $stages = $contract->stages()->whereIn('id', $stageIds)->lockForUpdate()->get();
        if ($stages->isEmpty() || $stages->count() !== count(array_unique($stageIds))) {
            throw new RuntimeException('اختر مرحلة واحدة على الأقل من هذا العقد');
        }
        if ($stages->contains(fn ($stage) => $stage->status !== ContractStage::STATUS_UNFUNDED)) {
            throw new RuntimeException('بعض المراحل المختارة ممولة بالفعل أو بانتظار الدفع');
        }

        return $stages;
    }

    private function hold(ContractStage $stage, string $source, ?OnlinePayment $online): void
    {
        $stage->update([
            'status' => ContractStage::STATUS_FUNDED,
            'funding_source' => $source,
            'online_payment_id' => $online?->id,
            'commission_rate' => RequestPaymentService::commissionRate(),
            'funded_at' => now(),
        ]);
        $contract = $stage->contract;
        $this->entry($contract->client_id, $stage, WalletTransaction::TYPE_ESCROW_IN, (float) $stage->amount,
            'تمويل المرحلة «' . $stage->title . '» من ' . $contract->number . ($source === 'balance' ? ' من رصيدك' : ' عبر EasyKash') . ' (يبقى المبلغ لدى نفّذها حتى تعتمد المرحلة)');
    }

    private function toClientBalance(int $clientId, float $amount, ?ContractStage $stage, string $description): void
    {
        $client = User::whereKey($clientId)->lockForUpdate()->first();
        $client->update(['balance' => round((float) $client->balance + $amount, 2)]);
        $this->entry($clientId, $stage, WalletTransaction::TYPE_REFUND, $amount, $description);
    }

    private function entry(?int $userId, ?ContractStage $stage, string $type, float $amount, string $description): void
    {
        WalletTransaction::create([
            'user_id' => $userId,
            'contract_stage_id' => $stage?->id,
            'type' => $type,
            'amount' => round($amount, 2),
            'description' => mb_substr($description, 0, 255),
        ]);
    }
}
