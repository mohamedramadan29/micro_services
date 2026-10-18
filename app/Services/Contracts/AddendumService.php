<?php

namespace App\Services\Contracts;

use App\Models\Contract;
use App\Models\ContractAddendum;
use App\Models\ContractAgreement;
use App\Models\ContractStage;
use App\Models\ServiceRequestEvent;
use App\Models\User;
use App\Notifications\RequestUpdateNotification;
use App\Support\Money;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Service 3: the addendum («ملحق») after the work started. Either side proposes, the other
 * approves or rejects; nothing changes before the approval. Allowed changes:
 * - add a stage at the end (unfunded, funded like any other);
 * - change a stage that is not settled: title, deliverable, acceptance, revisions (not below
 *   those used), duration (a running stage's deadline moves by the difference) and amount —
 *   freely while unfunded; once funded only lower (the difference goes back to the client's
 *   balance; for more money add a stage);
 * - remove an unfunded stage that has not started.
 * Not while a dispute is open (its stage money is frozen) or a stage is being paid.
 */
class AddendumService
{
    public const MAX_TOTAL_STAGES = 20;

    public function __construct(private StageWorkflow $workflow)
    {
    }

    public function propose(Contract $contract, User $by, array $changes, string $reason): ContractAddendum
    {
        $addendum = DB::transaction(function () use ($contract, $by, $changes, $reason) {
            $contract = Contract::whereKey($contract->id)->lockForUpdate()->first();
            $this->party($contract, $by);
            if ($contract->addenda()->where('status', ContractAddendum::STATUS_PROPOSED)->exists()) {
                throw new RuntimeException('يوجد ملحق مقترح بالفعل بانتظار الرد. اسحبه أو انتظر الرد أولًا.');
            }
            [$changes, $totalAfter] = $this->check($contract, $changes);

            $addendum = $contract->addenda()->create([
                'proposed_by' => $by->id,
                'status' => ContractAddendum::STATUS_PROPOSED,
                'changes' => $changes,
                'reason' => $reason,
                'total_before' => $this->total($contract),
                'total_after' => $totalAfter,
            ]);
            $this->log($contract, ($this->isClient($contract, $by) ? 'اقترح العميل' : 'اقترح المستقل') . ' ملحقًا: ' . implode('؛ ', $addendum->lines()) . ' — السبب: ' . $reason);

            return $addendum;
        });

        $contract = $addendum->contract;
        $other = $this->isClient($contract, $by) ? $contract->freelancer : $contract->client;
        $this->notify($other, \App\Support\Msg::t('contracts.addendum_proposed', ['number' => $contract->number, 'lines' => implode('؛ ', $addendum->lines())]), $contract);

        return $addendum;
    }

    /** The other side approves (the changes are applied now, checked again) or rejects. */
    public function respond(ContractAddendum $addendum, User $by, bool $approve, ?string $note = null): void
    {
        DB::transaction(function () use ($addendum, $by, $approve, $note) {
            $addendum = ContractAddendum::whereKey($addendum->id)->lockForUpdate()->first();
            $contract = Contract::whereKey($addendum->contract_id)->lockForUpdate()->first();
            $this->party($contract, $by);
            if (! $addendum->isOpen() || (int) $addendum->proposed_by === (int) $by->id) {
                throw new RuntimeException('هذا الملحق لم يعد بانتظار ردك');
            }
            if (! $approve) {
                $addendum->update(['status' => ContractAddendum::STATUS_REJECTED, 'responded_by' => $by->id, 'response_note' => $note, 'responded_at' => now()]);
                $this->log($contract, 'رُفض الملحق' . ($note ? ': ' . $note : ''));

                return;
            }

            [$changes] = $this->check($contract, $addendum->changes);
            $this->apply($contract, $addendum, $changes);
            $addendum->update(['status' => ContractAddendum::STATUS_APPROVED, 'responded_by' => $by->id, 'response_note' => $note, 'responded_at' => now(),
                'total_after' => $this->total($contract->fresh())]);
            $contract->request?->update(['agreed_price' => $addendum->total_after]);
            $this->log($contract, 'وافق الطرفان على الملحق وطُبّق: ' . implode('؛ ', $addendum->fresh()->lines()) . '. إجمالي المشروع صار ' . Money::format($addendum->total_after));
            $this->workflow->advance($contract->fresh());
        });

        $addendum->refresh();
        $contract = $addendum->contract;
        $this->notify($addendum->proposer, \App\Support\Msg::t('contracts.addendum_' . ($approve ? 'approved' : 'rejected') . ($note ? '_note' : ''), ['number' => $contract->number, 'note' => $note]), $contract);
    }

    public function cancel(ContractAddendum $addendum, User $by): void
    {
        DB::transaction(function () use ($addendum, $by) {
            $addendum = ContractAddendum::whereKey($addendum->id)->lockForUpdate()->first();
            if (! $addendum->isOpen() || (int) $addendum->proposed_by !== (int) $by->id) {
                throw new RuntimeException('لا يمكن سحب هذا الملحق');
            }
            $addendum->update(['status' => ContractAddendum::STATUS_CANCELLED]);
            $this->log($addendum->contract, 'سُحب الملحق المقترح');
        });
    }

    /**
     * Normalise and check the changes against the contract as it is now.
     *
     * @return array{0: array, 1: float} the changes and the project total after them
     */
    public function check(Contract $contract, array $changes): array
    {
        if ($contract->status !== Contract::STATUS_ACTIVE) {
            throw new RuntimeException('الملحق متاح فقط بعد الاتفاق وقبل اكتمال المشروع');
        }
        if ($contract->openDispute()) {
            throw new RuntimeException('على المشروع خلاف مفتوح: لا ملاحق حتى قرار الإدارة');
        }
        $stages = $contract->stages()->get()->keyBy('id');
        $amounts = $stages->map(fn ($s) => (float) $s->amount);
        $count = $stages->count();
        $removed = [];
        $touched = [];
        $clean = [];

        foreach (array_values($changes) as $c) {
            $type = $c['type'] ?? null;
            if ($type === 'add') {
                $count++;
                $clean[] = $add = [
                    'type' => 'add',
                    'title' => trim((string) ($c['title'] ?? '')),
                    'deliverable' => trim((string) ($c['deliverable'] ?? '')),
                    'acceptance' => trim((string) ($c['acceptance'] ?? '')) ?: null,
                    'duration_days' => (int) ($c['duration_days'] ?? 0),
                    'amount' => round((float) ($c['amount'] ?? 0), 2),
                    'revisions' => array_key_exists('revisions', $c) && $c['revisions'] === null ? null : (int) ($c['revisions'] ?? ContractPolicy::number('default_revisions')),
                ];
                if ($add['title'] === '' || mb_strlen($add['deliverable']) < 5 || $add['duration_days'] < 1 || $add['amount'] < 1) {
                    throw new RuntimeException('المرحلة الجديدة تحتاج عنوانًا وما يُسلَّم ومدة ومبلغًا');
                }
                $amounts->push($add['amount']);

                continue;
            }

            $stage = $stages[(int) ($c['stage_id'] ?? 0)] ?? null;
            if (! $stage || in_array($stage->id, $touched, true)) {
                throw new RuntimeException('المرحلة المطلوب تعديلها غير موجودة في هذا المشروع');
            }
            $touched[] = $stage->id;
            if ($stage->isSettled()) {
                throw new RuntimeException('المرحلة «' . $stage->title . '» انتهت ولا تُعدَّل');
            }
            if ($stage->status === ContractStage::STATUS_FUNDING) {
                throw new RuntimeException('المرحلة «' . $stage->title . '» قيد الدفع الآن؛ انتظر حتى يكتمل أو يفشل');
            }

            if ($type === 'remove') {
                if ($stage->status !== ContractStage::STATUS_UNFUNDED || $stage->started_at) {
                    throw new RuntimeException('تُحذف المرحلة فقط إن لم تُموَّل ولم تبدأ («' . $stage->title . '»)');
                }
                $removed[] = $stage->id;
                $amounts->forget($stage->id);
                $count--;
                $clean[] = ['type' => 'remove', 'stage_id' => $stage->id];

                continue;
            }
            if ($type !== 'change') {
                throw new RuntimeException('نوع تعديل غير معروف');
            }

            $edit = ['type' => 'change', 'stage_id' => $stage->id];
            foreach (['title', 'deliverable', 'acceptance'] as $field) {
                if (isset($c[$field]) && trim((string) $c[$field]) !== '' && trim((string) $c[$field]) !== (string) $stage->{$field}) {
                    $edit[$field] = trim((string) $c[$field]);
                }
            }
            if (isset($c['duration_days']) && $c['duration_days'] !== '' && (int) $c['duration_days'] !== (int) $stage->duration_days) {
                $days = (int) $c['duration_days'];
                if ($days < 1 || $days > 365) {
                    throw new RuntimeException('مدة المرحلة من يوم إلى 365 يومًا');
                }
                if ($stage->started_at && $stage->due_at->copy()->addDays($days - (int) $stage->duration_days)->isPast()) {
                    throw new RuntimeException('المدة الجديدة لـ«' . $stage->title . '» تجعل موعدها في الماضي');
                }
                $edit['duration_days'] = $days;
            }
            $revisions = array_key_exists('revisions', $c) && $c['revisions'] !== '' ? ($c['revisions'] === null ? null : (int) $c['revisions']) : $stage->revisions;
            if ($revisions !== $stage->revisions) {
                if ($revisions !== null && ($revisions < (int) $stage->revisions_used || $revisions > 20)) {
                    throw new RuntimeException('عدد تعديلات «' . $stage->title . '» لا يقل عمّا استُخدم (' . $stage->revisions_used . ') ولا يزيد على 20');
                }
                $edit['revisions'] = $revisions;
            }
            if (isset($c['amount']) && $c['amount'] !== '' && round((float) $c['amount'], 2) !== round((float) $stage->amount, 2)) {
                $amount = round((float) $c['amount'], 2);
                if ($amount < 1) {
                    throw new RuntimeException('مبلغ المرحلة 1 ج.م على الأقل');
                }
                if ($stage->status === ContractStage::STATUS_FUNDED && $amount > (float) $stage->amount) {
                    throw new RuntimeException('المرحلة «' . $stage->title . '» مموّلة: يمكن تخفيض مبلغها فقط. للزيادة أضف مرحلة إضافية بالفرق.');
                }
                $edit['amount'] = $amount;
                $amounts[$stage->id] = $amount;
            }
            if (count($edit) > 2) {
                $clean[] = $edit;
            }
        }

        if (! $clean) {
            throw new RuntimeException('لم تحدد أي تعديل');
        }
        if ($count > self::MAX_TOTAL_STAGES) {
            throw new RuntimeException('الحد الأقصى ' . self::MAX_TOTAL_STAGES . ' مرحلة في المشروع');
        }
        if ($stages->reject(fn ($s) => $s->isSettled() || in_array($s->id, $removed, true))->isEmpty() && ! collect($clean)->contains('type', 'add')) {
            throw new RuntimeException('لا يمكن حذف كل المراحل المتبقية؛ لإنهاء المشروع تواصل مع الإدارة');
        }

        return [$clean, round((float) $amounts->sum(), 2)];
    }

    private function apply(Contract $contract, ContractAddendum $addendum, array $changes): void
    {
        $ledger = app(ContractLedger::class);
        foreach ($changes as $c) {
            if ($c['type'] === 'add') {
                $contract->stages()->create([
                    'contract_addendum_id' => $addendum->id,
                    'sort_order' => (int) $contract->stages()->max('sort_order') + 1,
                    'title' => $c['title'],
                    'deliverable' => $c['deliverable'],
                    'acceptance' => $c['acceptance'],
                    'duration_days' => $c['duration_days'],
                    'amount' => $c['amount'],
                    'revisions' => $c['revisions'],
                    'status' => ContractStage::STATUS_UNFUNDED,
                ]);

                continue;
            }
            $stage = ContractStage::whereKey($c['stage_id'])->lockForUpdate()->first();
            if ($c['type'] === 'remove') {
                $stage->delete();

                continue;
            }
            $update = array_intersect_key($c, array_flip(['title', 'deliverable', 'acceptance', 'revisions']));
            if (isset($c['duration_days'])) {
                $update['duration_days'] = $c['duration_days'];
                if ($stage->started_at && $stage->due_at) {
                    $due = $stage->due_at->copy()->addDays($c['duration_days'] - (int) $stage->duration_days);
                    $update += ['due_at' => $due, 'due_reminder_sent_at' => null];
                    if ($due->isFuture()) {
                        $update += ['late_notified_at' => null, 'late_escalated_at' => null];
                    }
                }
            }
            if (isset($c['amount'])) {
                if ($stage->status === ContractStage::STATUS_FUNDED) {
                    $ledger->lowerHeld($stage, $c['amount'], 'ملحق على ' . $contract->number);
                } else {
                    $update['amount'] = $c['amount'];
                }
            }
            if ($update) {
                $stage->update($update);
            }
        }
        // Keep the order compact (1, 2, 3…) after removals.
        foreach ($contract->stages()->get()->values() as $index => $stage) {
            if ((int) $stage->sort_order !== $index + 1) {
                $stage->update(['sort_order' => $index + 1]);
            }
        }
    }

    private function total(Contract $contract): float
    {
        return round((float) $contract->stages()->sum('amount'), 2);
    }

    private function party(Contract $contract, User $user): void
    {
        if (! in_array((int) $user->id, [(int) $contract->client_id, (int) $contract->freelancer_id], true)) {
            throw new RuntimeException('الملحق بين العميل والمستقل فقط');
        }
    }

    private function isClient(Contract $contract, User $user): bool
    {
        return (int) $contract->client_id === (int) $user->id;
    }

    private function log(Contract $contract, string $note): void
    {
        if ($contract->request) {
            ServiceRequestEvent::record($contract->request, ServiceRequestEvent::TYPE_AGREEMENT, ['note' => mb_substr($note, 0, 2000)]);
        }
    }

    private function notify(?User $user, string $text, Contract $contract): void
    {
        DB::afterCommit(fn () => $user?->notify(new RequestUpdateNotification(mb_substr($text, 0, 500), '/contracts/' . $contract->number, 'contracts')));
    }
}
