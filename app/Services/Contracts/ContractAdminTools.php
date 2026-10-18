<?php

namespace App\Services\Contracts;

use App\Models\Contract;
use App\Models\ContractDelivery;
use App\Models\ContractPause;
use App\Models\ContractStage;
use App\Models\ServiceRequestEvent;
use App\Models\User;
use App\Notifications\RequestUpdateNotification;
use App\Support\Money;
use App\Support\StaffPermissions;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Service 3: what the team can do on a contract without a dispute, answering the alerts of
 * `contracts:deadlines` (late stage, delivery not reviewed, pause without an answer). Every
 * action needs a written reason, is logged on the request and told to both sides.
 * Approving on the client's behalf moves money: only a role that may do it directly
 * (manager, or accounts below the threshold).
 */
class ContractAdminTools
{
    public function __construct(private StageWorkflow $workflow, private ContractLedger $ledger)
    {
    }

    /** Move a running stage's deadline (lateness alerts start over when it is in the future again). */
    public function extendDeadline(ContractStage $stage, int $days, string $reason, User $admin): void
    {
        $this->allow($admin, false, 0);
        DB::transaction(function () use ($stage, $days, $reason) {
            $stage = ContractStage::whereKey($stage->id)->lockForUpdate()->first();
            if ($stage->isSettled() || ! $stage->started_at || ! $stage->due_at) {
                throw new RuntimeException('يُمدّ موعد مرحلة بدأت ولم تنتهِ فقط');
            }
            if ($days < 1 || $days > 90) {
                throw new RuntimeException('التمديد من يوم إلى 90 يومًا');
            }
            $due = $stage->due_at->copy()->addDays($days);
            $stage->update(['due_at' => $due, 'due_reminder_sent_at' => null] + ($due->isFuture() ? ['late_notified_at' => null, 'late_escalated_at' => null] : []));
            $this->log($stage->contract, 'مدّت الإدارة موعد المرحلة «' . $stage->title . '» ' . $days . ' يوم إلى ' . $due->format('Y-m-d H:i') . ': ' . $reason);
        });

        $stage->refresh();
        $this->tellBoth($stage->contract, \App\Support\Msg::t('contracts.admin_extended_deadline', ['stage' => $stage->title, 'number' => $stage->contract->number, 'date' => $stage->due_at->format('Y-m-d'), 'reason' => $reason]));
    }

    /** The client never reviewed a delivery: the team approves it after checking it (documented). */
    public function approveForClient(ContractDelivery $delivery, string $reason, User $admin): ContractStage
    {
        $this->allow($admin, true, (float) $delivery->stage->amount);
        $stage = DB::transaction(function () use ($delivery, $reason) {
            $delivery = ContractDelivery::whereKey($delivery->id)->lockForUpdate()->first();
            $stage = ContractStage::whereKey($delivery->contract_stage_id)->lockForUpdate()->first();
            $contract = $stage->contract;
            if ($delivery->status !== ContractDelivery::STATUS_SUBMITTED || $stage->status !== ContractStage::STATUS_FUNDED) {
                throw new RuntimeException('هذا التسليم لم يعد بانتظار المراجعة');
            }
            if ($contract->openDispute()) {
                throw new RuntimeException('على المشروع خلاف مفتوح: احسمه من نموذج الخلاف');
            }
            $delivery->update(['status' => ContractDelivery::STATUS_APPROVED, 'reviewed_at' => now(), 'client_note' => 'اعتمدته الإدارة: ' . $reason]);
            $stage = $this->ledger->release($stage, 'اعتمدت الإدارة التسليم نيابة عن العميل');
            $stage->update(['delivered_at' => null]);
            $this->log($contract, 'اعتمدت الإدارة المرحلة «' . $stage->title . '» نيابة عن العميل وحُوّل ' . Money::format($stage->freelancer_amount) . ' للمستقل: ' . $reason);
            $this->workflow->afterSettlement($contract);

            return $stage;
        });

        $this->tellBoth($stage->contract, \App\Support\Msg::t('contracts.admin_approved_stage', ['stage' => $stage->title, 'number' => $stage->contract->number, 'reason' => $reason]));

        return $stage;
    }

    /** End a pause the client never answered: with the deadline moved by the paused time, or without. */
    public function endPause(ContractPause $pause, bool $extend, string $reason, User $admin): void
    {
        $this->allow($admin, false, 0);
        DB::transaction(function () use ($pause, $extend, $reason) {
            $pause = ContractPause::whereKey($pause->id)->lockForUpdate()->first();
            if ($pause->status !== ContractPause::STATUS_ACTIVE) {
                throw new RuntimeException('هذا الإيقاف لم يعد قائمًا');
            }
            $minutes = $extend ? (int) ceil($pause->started_at->diffInSeconds(now()) / 60) : 0;
            $pause->update(['status' => ContractPause::STATUS_RESUMED, 'ended_at' => now(), 'extended_minutes' => $minutes]);
            $stage = ContractStage::whereKey($pause->contract_stage_id)->lockForUpdate()->first();
            if ($minutes && $stage->due_at) {
                $due = $stage->due_at->copy()->addMinutes($minutes);
                $stage->update(['due_at' => $due, 'due_reminder_sent_at' => null] + ($due->isFuture() ? ['late_notified_at' => null, 'late_escalated_at' => null] : []));
            }
            $this->log($stage->contract, 'أنهت الإدارة إيقاف العدّاد للمرحلة «' . $stage->title . '» ' . ($extend ? 'مع تمديد الموعد إلى ' . $stage->due_at?->format('Y-m-d H:i') : 'دون تمديد') . ': ' . $reason);
        });

        $contract = $pause->stage->contract;
        $this->tellBoth($contract, \App\Support\Msg::t($extend ? 'contracts.admin_ended_pause_extended' : 'contracts.admin_ended_pause', ['number' => $contract->number, 'reason' => $reason]));
    }

    /** «طلب رد من الطرفين»: a question to both sides with a deadline, outside a dispute. */
    public function requestReply(Contract $contract, string $question, int $days, User $admin): \App\Models\ContractReplyRequest
    {
        $this->allow($admin, false, 0);
        if (! in_array($contract->status, [Contract::STATUS_AGREEMENT, Contract::STATUS_ACTIVE], true)) {
            throw new RuntimeException('المشروع ليس قيد التنفيذ');
        }
        if ($days < 1 || $days > 30) {
            throw new RuntimeException('المهلة من يوم إلى 30 يومًا');
        }
        $reply = $contract->replyRequests()->create(['requested_by' => $admin->id, 'question' => $question, 'due_at' => now()->addDays($days)]);
        $this->log($contract, 'طلبت الإدارة ردًا من الطرفين حتى ' . $reply->due_at->format('Y-m-d') . ': ' . $question);
        $this->tellBoth($contract, \App\Support\Msg::t('contracts.admin_reply_requested', ['number' => $contract->number, 'date' => $reply->due_at->format('Y-m-d'), 'question' => $question]));

        return $reply;
    }

    /** The client or the freelancer answers (once each; the answer can be corrected until the team closes it). */
    public function reply(\App\Models\ContractReplyRequest $reply, User $user, string $text): void
    {
        $contract = $reply->contract;
        $side = match ((int) $user->id) {
            (int) $contract->client_id => 'client',
            (int) $contract->freelancer_id => 'freelancer',
            default => throw new RuntimeException('غير مسموح'),
        };
        if (! $reply->isOpen()) {
            throw new RuntimeException('أُغلق هذا الطلب');
        }
        $reply->update([$side . '_reply' => $text, $side . '_replied_at' => now()]);
        $this->log($contract, ($side === 'client' ? 'ردّ العميل' : 'ردّ المستقل') . ' على طلب الإدارة: ' . $text);
        \Illuminate\Support\Facades\Notification::send(User::activeAdmins()->get(), new RequestUpdateNotification(
            \App\Support\Msg::t($side === 'client' ? 'contracts.admin_reply_from_client' : 'contracts.admin_reply_from_freelancer', ['number' => $contract->number]), '/contracts/' . $contract->number, 'admin_alerts'));
    }

    public function closeReply(\App\Models\ContractReplyRequest $reply, User $admin): void
    {
        $this->allow($admin, false, 0);
        $reply->update(['closed_at' => now()]);
    }

    /** Requests past their deadline without both answers: the team is told once. */
    public function tick(): int
    {
        $count = 0;
        foreach (\App\Models\ContractReplyRequest::whereNull('closed_at')->whereNull('overdue_alerted_at')->where('due_at', '<=', now())->with('contract')->get() as $reply) {
            $reply->update(['overdue_alerted_at' => now()]);
            if ($reply->answered()) {
                continue;
            }
            $missing = collect(['العميل' => $reply->client_replied_at, 'المستقل' => $reply->freelancer_replied_at])->filter(fn ($at) => ! $at)->keys()->implode(' و');
            \Illuminate\Support\Facades\Notification::send(User::activeAdmins()->get(), new RequestUpdateNotification(
                \App\Support\Msg::t('contracts.admin_reply_overdue', ['number' => $reply->contract->number, 'missing' => $missing]), '/contracts/' . $reply->contract->number, 'admin_alerts'));
            $count++;
        }

        return $count;
    }

    private function allow(User $admin, bool $money, float $amount): void
    {
        $mode = StaffPermissions::mode($admin, $money, $amount);
        if ($mode !== 'direct') {
            throw new RuntimeException($money ? 'هذا القرار يحوّل أموالًا: ينفّذه المدير أو المحاسب في حدود صلاحيته.' : 'هذا الإجراء ليس من صلاحيات دورك.');
        }
    }

    private function tellBoth(Contract $contract, string $text): void
    {
        foreach ([$contract->client, $contract->freelancer] as $user) {
            $user?->notify(new RequestUpdateNotification(mb_substr($text, 0, 500), '/contracts/' . $contract->number, 'contracts'));
        }
    }

    private function log(Contract $contract, string $note): void
    {
        if ($contract->request) {
            ServiceRequestEvent::record($contract->request, ServiceRequestEvent::TYPE_AGREEMENT, ['note' => mb_substr($note, 0, 2000)]);
        }
    }
}
