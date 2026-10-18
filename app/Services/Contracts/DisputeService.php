<?php

namespace App\Services\Contracts;

use App\Models\Contract;
use App\Models\ContractDelivery;
use App\Models\ContractDeliveryFile;
use App\Models\ContractDispute;
use App\Models\ContractStage;
use App\Models\ServiceRequestEvent;
use App\Models\User;
use App\Notifications\RequestUpdateNotification;
use App\Support\Msg;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

/**
 * Service 3, phase 5 (client decisions 2026-10-16): either side opens a dispute with a reason,
 * a description and evidence; the other side has 3 days to answer; the money of the stage stays
 * frozen; the admin decides (continue, revision with a deadline, release, refund, split by %,
 * change the freelancer) with a written reason.
 */
class DisputeService
{
    public function __construct(private ContractLedger $ledger, private StageWorkflow $workflow)
    {
    }

    /** @param  UploadedFile[]  $files */
    public function open(Contract $contract, User $user, string $reason, string $description, array $files = []): ContractDispute
    {
        $paths = [];
        try {
            $dispute = DB::transaction(function () use ($contract, $user, $reason, $description, $files, &$paths) {
                $contract = Contract::whereKey($contract->id)->lockForUpdate()->first();
                if (! $this->isParty($contract, $user) || $contract->status !== Contract::STATUS_ACTIVE) {
                    throw new RuntimeException('لا يمكن فتح خلاف على هذا العقد');
                }
                if ($contract->openDispute()) {
                    throw new RuntimeException('يوجد خلاف مفتوح على هذا العقد بالفعل');
                }
                $dispute = $contract->disputes()->create([
                    'contract_stage_id' => $this->workflow->currentStage($contract)?->id,
                    'opened_by' => $user->id,
                    'reason' => $reason,
                    'description' => $description,
                    'status' => ContractDispute::STATUS_OPEN,
                    'respond_due_at' => now()->addDays(ContractPolicy::number('dispute_respond_days')),
                ]);
                $paths = $this->storeFiles($dispute, $files);
                $this->log($contract, 'فتح ' . $this->role($contract, $user) . ' خلافًا (' . ContractPolicy::disputeReasonLabel($reason) . '): ' . $description);

                return $dispute;
            });
        } catch (\Throwable $e) {
            Storage::disk(ContractDeliveryFile::DISK)->delete($paths);
            throw $e;
        }

        $other = $this->otherParty($contract, $user);
        $this->notify($other, Msg::t('disputes.opened', ['number' => $contract->number, 'reason' => ContractPolicy::disputeReasonLabel($reason), 'days' => ContractPolicy::number('dispute_respond_days')]), $contract);
        $this->notifyAdmins(Msg::t('disputes.admin_opened', ['number' => $contract->number, 'reason' => ContractPolicy::disputeReasonLabel($reason)]), $contract);

        return $dispute;
    }

    /** @param  UploadedFile[]  $files  The other side answers: the admin decides next. */
    public function respond(ContractDispute $dispute, User $user, string $response, array $files = []): void
    {
        $paths = [];
        try {
            DB::transaction(function () use ($dispute, $user, $response, $files, &$paths) {
                $dispute = ContractDispute::whereKey($dispute->id)->lockForUpdate()->first();
                $contract = $dispute->contract;
                if ($dispute->status !== ContractDispute::STATUS_OPEN || ! $this->isParty($contract, $user) || (int) $dispute->opened_by === (int) $user->id) {
                    throw new RuntimeException('لا يمكنك الرد على هذا الخلاف الآن');
                }
                $paths = $this->storeFiles($dispute, $files);
                $dispute->update(['response' => $response, 'responded_at' => now(), 'status' => ContractDispute::STATUS_AWAITING]);
                $this->log($contract, 'ردّ ' . $this->role($contract, $user) . ' على الخلاف: ' . $response);
            });
        } catch (\Throwable $e) {
            Storage::disk(ContractDeliveryFile::DISK)->delete($paths);
            throw $e;
        }
        $contract = $dispute->contract;
        $this->notify($dispute->opener, Msg::t('disputes.responded', ['number' => $contract->number]), $contract);
        $this->notifyAdmins(Msg::t('disputes.admin_responded', ['number' => $contract->number]), $contract);
    }

    public function withdraw(ContractDispute $dispute, User $user): void
    {
        DB::transaction(function () use ($dispute, $user) {
            $dispute = ContractDispute::whereKey($dispute->id)->lockForUpdate()->first();
            if (! $dispute->isOpen() || (int) $dispute->opened_by !== (int) $user->id) {
                throw new RuntimeException('لا يمكن سحب هذا الخلاف');
            }
            $dispute->update(['status' => ContractDispute::STATUS_WITHDRAWN]);
            $this->log($dispute->contract, 'سُحب الخلاف');
        });
        $this->notify($this->otherParty($dispute->contract, $user), Msg::t('disputes.withdrawn', ['number' => $dispute->contract->number]), $dispute->contract);
    }

    /** The admin's decision (always with a written reason). */
    public function decide(ContractDispute $dispute, User $admin, string $decision, string $note, ?int $percent = null, ?string $revisionDue = null): void
    {
        $contract = DB::transaction(function () use ($dispute, $admin, $decision, $note, $percent, $revisionDue) {
            $dispute = ContractDispute::whereKey($dispute->id)->lockForUpdate()->first();
            if (! $dispute->isOpen()) {
                throw new RuntimeException('هذا الخلاف ليس مفتوحًا');
            }
            $contract = $dispute->contract;
            $stage = $dispute->stage ? ContractStage::whereKey($dispute->stage->id)->lockForUpdate()->first() : null;
            $money = in_array($decision, ['release', 'refund', 'split'], true);
            if ($money && (! $stage || $stage->status !== ContractStage::STATUS_FUNDED)) {
                throw new RuntimeException('لا يوجد مبلغ مموّل في مرحلة الخلاف ليُحرَّر أو يُسترجع أو يُقسَّم');
            }
            // Settle first: the admin's money decisions bypass the freeze, then the dispute closes.
            $dispute->update([
                'status' => ContractDispute::STATUS_DECIDED, 'decision' => $decision, 'decision_note' => $note,
                'freelancer_percent' => $decision === 'split' || $decision === 'change_freelancer' ? $percent : null,
                'revision_due' => $decision === 'revision' ? $revisionDue : null,
                'decided_by' => $admin->id, 'decided_at' => now(),
            ]);

            match ($decision) {
                'continue' => null,
                'revision' => $this->orderRevision($stage, $note, $revisionDue),
                'release' => $this->settleAndAdvance($stage, fn () => $this->ledger->release($stage, 'قرار الإدارة في خلاف: ' . $note)),
                'refund' => $this->settleAndAdvance($stage, fn () => $this->ledger->refund($stage, 'قرار الإدارة في خلاف: ' . $note), false),
                'split' => $this->settleAndAdvance($stage, fn () => $this->ledger->split($stage, round((float) $stage->amount * $percent / 100, 2), 'قرار الإدارة في خلاف: ' . $note)),
                'change_freelancer' => app(FreelancerChangeService::class)->apply(
                    $contract->freelancerChanges()->create(['requested_by' => $admin->id, 'kind' => 'admin', 'reason' => 'قرار خلاف: ' . $note, 'leaving_freelancer_id' => $contract->freelancer_id]),
                    $admin, (int) $percent, $note
                ),
            };
            $this->log($contract, 'قررت الإدارة في الخلاف: ' . ContractDispute::DECISIONS[$decision]
                . ($decision === 'split' ? ' (' . $percent . '% للمستقل)' : '') . ($decision === 'revision' ? ' حتى ' . $revisionDue : '') . ' — ' . $note);

            return $contract;
        });

        $text = Msg::t('disputes.decided', ['number' => $contract->number, 'decision' => ContractDispute::DECISIONS[$decision], 'note' => $note]);
        $this->notify($contract->client, $text, $contract);
        $this->notify($contract->freelancer, $text, $contract);
    }

    /** Money moved by a decision (for staff permissions): the frozen stage, or everything held on a change. */
    public function amountAt(ContractDispute $dispute, string $decision): float
    {
        return match (true) {
            $decision === 'change_freelancer' => (float) $dispute->contract->totals()['held'],
            in_array($decision, ['release', 'refund', 'split'], true) => (float) ($dispute->stage?->amount ?? 0),
            default => 0.0,
        };
    }

    /** A money decision saved for approval (staff roles). */
    public function propose(ContractDispute $dispute, User $admin, array $decision): void
    {
        if (! $dispute->isOpen()) {
            throw new RuntimeException('هذا الخلاف ليس مفتوحًا');
        }
        $dispute->update(['proposed' => $decision + ['amount' => $this->amountAt($dispute, $decision['decision'])], 'proposed_by' => $admin->id]);
        $this->log($dispute->contract, 'اقترح ' . $admin->name . ' قرارًا في الخلاف بانتظار الاعتماد: ' . ContractDispute::DECISIONS[$decision['decision']] . ' — ' . $decision['note']);
        $this->notifyApprovers(Msg::t('disputes.admin_proposal', ['number' => $dispute->contract->number, 'decision' => ContractDispute::DECISIONS[$decision['decision']]]), $dispute->contract);
    }

    /** An approver carries out the proposed decision (or drops it). */
    public function settleProposal(ContractDispute $dispute, User $approver, bool $approve): void
    {
        $proposed = $dispute->proposed;
        if (! $proposed) {
            throw new RuntimeException('لا يوجد قرار مقترح');
        }
        DB::transaction(function () use ($dispute, $approver, $approve, $proposed) {
            $dispute->update(['proposed' => null, 'proposed_by' => null]);
            if ($approve) {
                $this->decide($dispute->fresh(), $approver, $proposed['decision'], $proposed['note'] . ' (اعتمده ' . $approver->name . ')', $proposed['percent'] ?? null, $proposed['revision_due'] ?? null);
            } else {
                $this->log($dispute->contract, 'رفض ' . $approver->name . ' القرار المقترح في الخلاف');
            }
        });
    }

    /** Disputes past their 3 days without an answer go to the admin. */
    public function tick(): int
    {
        $count = 0;
        foreach (ContractDispute::where('status', ContractDispute::STATUS_OPEN)->where('respond_due_at', '<=', now())->with('contract')->get() as $dispute) {
            $dispute->update(['status' => ContractDispute::STATUS_AWAITING]);
            $this->log($dispute->contract, 'انتهت مهلة الرد على الخلاف؛ تقرر الإدارة بما لديها من أدلة');
            $this->notifyAdmins(Msg::t('disputes.admin_respond_overdue', ['number' => $dispute->contract->number]), $dispute->contract);
            $count++;
        }

        return $count;
    }

    private function orderRevision(?ContractStage $stage, string $note, ?string $due): void
    {
        if (! $stage || ! $due) {
            throw new RuntimeException('حدد موعد التعديل');
        }
        $stage->deliveries()->where('status', ContractDelivery::STATUS_SUBMITTED)
            ->update(['status' => ContractDelivery::STATUS_REVISION, 'client_note' => 'قرار الإدارة: ' . $note, 'reviewed_at' => now()]);
        // A decided revision does not use one of the client's agreed revisions.
        $stage->update(['due_at' => Carbon::parse($due)->endOfDay(), 'delivered_at' => null, 'due_reminder_sent_at' => null, 'late_notified_at' => null, 'late_escalated_at' => null]);
    }

    private function settleAndAdvance(ContractStage $stage, callable $settle, bool $accepted = true): void
    {
        // Release/split count as the work being accepted; a refund leaves the delivery marked as not accepted.
        $stage->deliveries()->where('status', ContractDelivery::STATUS_SUBMITTED)->update([
            'status' => $accepted ? ContractDelivery::STATUS_APPROVED : ContractDelivery::STATUS_REVISION,
            'reviewed_at' => now(),
        ] + ($accepted ? [] : ['client_note' => 'قرار الإدارة: استرجاع مبلغ المرحلة']));
        $settle();
        $this->workflow->afterSettlement(Contract::find($stage->contract_id));
    }

    /** @return string[] stored paths */
    private function storeFiles(ContractDispute $dispute, array $files): array
    {
        $paths = [];
        foreach ($files as $file) {
            $path = $file->store('contracts/' . $dispute->contract_id . '/dispute-' . $dispute->id, ContractDeliveryFile::DISK);
            $paths[] = $path;
            $dispute->files()->create(['original_name' => mb_substr($file->getClientOriginalName(), 0, 255), 'path' => $path, 'mime' => $file->getClientMimeType(), 'size' => $file->getSize()]);
        }

        return $paths;
    }

    private function isParty(Contract $contract, User $user): bool
    {
        return in_array((int) $user->id, [(int) $contract->client_id, (int) $contract->freelancer_id], true);
    }

    private function role(Contract $contract, User $user): string
    {
        return (int) $user->id === (int) $contract->client_id ? 'العميل' : 'المستقل';
    }

    private function otherParty(Contract $contract, User $user): ?User
    {
        return (int) $user->id === (int) $contract->client_id ? $contract->freelancer : $contract->client;
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

    private function notifyApprovers(string $text, Contract $contract): void
    {
        $approvers = User::activeAdmins()->get()->filter(fn (User $u) => in_array(\App\Support\StaffPermissions::role($u), ['manager', 'accounts'], true));
        DB::afterCommit(fn () => Notification::send($approvers, new RequestUpdateNotification($text, '/contracts/' . $contract->number, 'admin_alerts')));
    }

    private function notifyAdmins(string $text, Contract $contract): void
    {
        DB::afterCommit(fn () => Notification::send(User::activeAdmins()->get(), new RequestUpdateNotification($text, '/contracts/' . $contract->number, 'admin_alerts')));
    }
}
