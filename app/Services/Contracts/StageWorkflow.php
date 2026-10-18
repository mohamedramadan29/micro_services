<?php

namespace App\Services\Contracts;

use App\Models\Contract;
use App\Models\ContractDelivery;
use App\Models\ContractDeliveryFile;
use App\Models\ContractStage;
use App\Models\ServiceRequest;
use App\Models\ServiceRequestEvent;
use App\Models\User;
use App\Notifications\RequestUpdateNotification;
use App\Support\Money;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

/**
 * Service 3, phase 3: stages run in order. The current stage = the first one not settled; it
 * starts (deadline from then) only when funded and the previous one is approved. The freelancer
 * delivers; the client approves (ContractLedger releases the money) or asks for a revision
 * within the agreed number. The last approval completes the contract and its request.
 */
class StageWorkflow
{
    public function __construct(private ContractLedger $ledger)
    {
    }

    public function currentStage(Contract $contract): ?ContractStage
    {
        return $contract->stages()->whereNotIn('status', [ContractStage::STATUS_RELEASED, ContractStage::STATUS_REFUNDED, ContractStage::STATUS_SPLIT])->first();
    }

    /** Start the current stage when it is funded (call after any funding or approval). */
    public function advance(Contract $contract): ?ContractStage
    {
        $stage = $this->currentStage($contract);
        // The next stage waits for money: ask the client once to fund it.
        if ($stage && $stage->status === ContractStage::STATUS_UNFUNDED && ! $stage->funding_requested_at
            && $contract->status === Contract::STATUS_ACTIVE && $stage->sort_order > 1) {
            $stage->update(['funding_requested_at' => now()]);
            $this->notify($contract->client, \App\Support\Msg::t('contracts.stage_awaiting_funding_client', ['stage' => $stage->title, 'number' => $contract->number, 'amount' => Money::format($stage->amount)]), $contract);
            $this->notify($contract->freelancer, \App\Support\Msg::t('contracts.stage_awaiting_funding_freelancer', ['stage' => $stage->title, 'number' => $contract->number]), $contract);
        }
        if (! $stage || $stage->status !== ContractStage::STATUS_FUNDED || $stage->started_at) {
            return null;
        }
        $stage->update(['started_at' => now(), 'due_at' => now()->addDays((int) $stage->duration_days)]);
        $this->log($contract, 'بدأت المرحلة «' . $stage->title . '»، موعد تسليمها ' . $stage->due_at->format('Y-m-d'));
        $this->notify($contract->freelancer, \App\Support\Msg::t('contracts.stage_started', ['stage' => $stage->title, 'number' => $contract->number, 'date' => $stage->due_at->format('Y-m-d')]), $contract);

        return $stage;
    }

    /** @param  UploadedFile[]  $files */
    public function deliver(ContractStage $stage, User $freelancer, string $note, array $links, array $files): ContractDelivery
    {
        $paths = [];
        try {
            $delivery = DB::transaction(function () use ($stage, $freelancer, $note, $links, $files, &$paths) {
                $stage = ContractStage::whereKey($stage->id)->lockForUpdate()->first();
                $contract = $stage->contract;
                if ((int) $contract->freelancer_id !== (int) $freelancer->id || $contract->status !== Contract::STATUS_ACTIVE
                    || $stage->status !== ContractStage::STATUS_FUNDED || ! $stage->started_at) {
                    throw new RuntimeException('لا يمكن تسليم هذه المرحلة الآن (يجب أن تكون المرحلة الحالية ومموّلة)');
                }
                if ($stage->deliveries()->where('status', ContractDelivery::STATUS_SUBMITTED)->exists()) {
                    throw new RuntimeException('تسليمك السابق ما زال بانتظار مراجعة العميل');
                }
                app(StageClock::class)->endOnDelivery($stage);

                $delivery = $stage->deliveries()->create([
                    'freelancer_id' => $freelancer->id,
                    'note' => $note,
                    'links' => array_values($links) ?: null,
                    'status' => ContractDelivery::STATUS_SUBMITTED,
                    'review_due_at' => now()->addDays(ContractPolicy::number('review_days')),
                ]);
                foreach ($files as $file) {
                    $path = $file->store('contracts/' . $contract->id . '/stage-' . $stage->id, ContractDeliveryFile::DISK);
                    $paths[] = $path;
                    $delivery->files()->create([
                        'original_name' => mb_substr($file->getClientOriginalName(), 0, 255),
                        'path' => $path,
                        'mime' => $file->getClientMimeType(),
                        'size' => $file->getSize(),
                    ]);
                }
                $stage->update(['delivered_at' => now()]);
                $this->log($contract, 'سلّم المستقل المرحلة «' . $stage->title . '»');

                return $delivery;
            });
        } catch (\Throwable $e) {
            Storage::disk(ContractDeliveryFile::DISK)->delete($paths);
            throw $e;
        }

        $contract = $delivery->stage->contract;
        $this->notify($contract->client, \App\Support\Msg::t('contracts.stage_delivered', ['stage' => $delivery->stage->title, 'number' => $contract->number, 'days' => ContractPolicy::number('review_days')]), $contract);

        return $delivery;
    }

    /** The client approves the delivery: the stage money goes to the freelancer, the next stage may start. */
    public function approve(ContractDelivery $delivery, User $client): ContractStage
    {
        $stage = DB::transaction(function () use ($delivery, $client) {
            $delivery = $this->pendingDelivery($delivery, $client);
            $delivery->update(['status' => ContractDelivery::STATUS_APPROVED, 'reviewed_at' => now()]);
            $stage = $this->ledger->release($delivery->stage, 'اعتمد العميل التسليم');
            $stage->update(['delivered_at' => null]);
            $contract = Contract::find($stage->contract_id);
            $this->log($contract, 'اعتمد العميل المرحلة «' . $stage->title . '» وحُوّل ' . Money::format($stage->freelancer_amount) . ' للمستقل');
            $this->afterSettlement($contract);

            return $stage;
        });

        $contract = $stage->contract;
        $this->notify($contract->freelancer, \App\Support\Msg::t($contract->status === Contract::STATUS_COMPLETED ? 'contracts.stage_approved_completed' : 'contracts.stage_approved',
            ['stage' => $stage->title, 'number' => $contract->number, 'amount' => Money::format($stage->freelancer_amount)]), $contract);

        return $stage;
    }

    /** After a stage is settled (approved, or decided in a dispute): complete the contract or start the next stage. */
    public function afterSettlement(Contract $contract): void
    {
        $contract->refresh();
        if ($contract->status === Contract::STATUS_COMPLETED && $contract->request && $contract->request->status !== ServiceRequest::STATUS_COMPLETED) {
            $contract->request->statusChangeNote = 'اعتُمدت كل مراحل العقد ' . $contract->number;
            $contract->request->update(['status' => ServiceRequest::STATUS_COMPLETED, 'completed_at' => now()]);
        } elseif ($contract->status === Contract::STATUS_CANCELLED && $contract->request && $contract->request->status !== ServiceRequest::STATUS_NOT_COMPLETED) {
            $contract->request->statusChangeNote = 'انتهى العقد ' . $contract->number . ' باسترجاع المرحلة الأخيرة بقرار الإدارة';
            $contract->request->update(['status' => ServiceRequest::STATUS_NOT_COMPLETED, 'closed_reason' => 'not_feasible', 'closed_at' => now()]);
        } else {
            $this->advance($contract);
        }
    }

    /** The client asks for a revision, within the agreed number (beyond it: an addendum or a dispute). */
    public function requestRevision(ContractDelivery $delivery, User $client, string $note): void
    {
        DB::transaction(function () use ($delivery, $client, $note) {
            $delivery = $this->pendingDelivery($delivery, $client);
            $stage = ContractStage::whereKey($delivery->contract_stage_id)->lockForUpdate()->first();
            if ($stage->revisionsLeft() === 0) {
                throw new RuntimeException('انتهت التعديلات المتفق عليها لهذه المرحلة. يمكنك الاتفاق مع المستقل على ملحق، أو فتح خلاف لتحسمه الإدارة.');
            }
            $delivery->update(['status' => ContractDelivery::STATUS_REVISION, 'client_note' => $note, 'reviewed_at' => now()]);
            $stage->update(['revisions_used' => $stage->revisions_used + 1, 'delivered_at' => null]);
            $this->log($stage->contract, 'طلب العميل تعديلًا على المرحلة «' . $stage->title . '» (' . $stage->revisions_used . ($stage->revisions === null ? '' : ' من ' . $stage->revisions) . '): ' . $note);
        });

        $stage = $delivery->stage->fresh();
        $this->notify($stage->contract->freelancer, \App\Support\Msg::t('contracts.stage_revision_requested', ['stage' => $stage->title, 'number' => $stage->contract->number, 'note' => $note]), $stage->contract);
    }

    private function pendingDelivery(ContractDelivery $delivery, User $client): ContractDelivery
    {
        $delivery = ContractDelivery::whereKey($delivery->id)->lockForUpdate()->first();
        $contract = $delivery->stage->contract;
        if ((int) $contract->client_id !== (int) $client->id || $delivery->status !== ContractDelivery::STATUS_SUBMITTED
            || $delivery->stage->status !== ContractStage::STATUS_FUNDED) {
            throw new RuntimeException('هذا التسليم لم يعد بانتظار مراجعتك');
        }
        if ($contract->openDispute()) {
            throw new RuntimeException('على المشروع خلاف مفتوح: مبلغ المرحلة مجمّد حتى قرار الإدارة');
        }

        return $delivery;
    }

    private function log(Contract $contract, string $note): void
    {
        if ($contract->request) {
            ServiceRequestEvent::record($contract->request, ServiceRequestEvent::TYPE_AGREEMENT, ['note' => $note]);
        }
    }

    private function notify(?User $user, string $text, Contract $contract): void
    {
        DB::afterCommit(fn () => $user?->notify(new RequestUpdateNotification($text, '/contracts/' . $contract->number, 'contracts')));
    }
}
