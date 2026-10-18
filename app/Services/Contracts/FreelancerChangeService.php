<?php

namespace App\Services\Contracts;

use App\Models\Contract;
use App\Models\ContractDelivery;
use App\Models\ContractDeliveryFile;
use App\Models\ContractFreelancerChange as Change;
use App\Models\ContractStage;
use App\Models\ServiceRequest;
use App\Models\ServiceRequestEvent;
use App\Models\ServiceRequestFile;
use App\Models\User;
use App\Notifications\RequestUpdateNotification;
use App\Support\Msg;
use App\Support\Money;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

/**
 * Changing the freelancer (client decisions 2026-10-16): the client after 7 late days, the
 * freelancer withdrawing with a reason, or the admin directly — always decided by the admin.
 * On approval the leaving freelancer gets the admin-set % of the current unapproved stage
 * (the rest to the client's balance), stages funded ahead go back to the client's balance,
 * approved stages are untouched, and the remaining work is published as a new staged request
 * (what was done + the files) for new offers and a new agreement (the addendum).
 */
class FreelancerChangeService
{
    public function __construct(private ContractLedger $ledger, private StageWorkflow $workflow)
    {
    }

    public function request(Contract $contract, User $user, string $kind, string $reason): Change
    {
        $change = DB::transaction(function () use ($contract, $user, $kind, $reason) {
            $contract = Contract::whereKey($contract->id)->lockForUpdate()->first();
            if ($contract->status !== Contract::STATUS_ACTIVE) {
                throw new RuntimeException('العقد ليس قيد التنفيذ');
            }
            if ($contract->freelancerChanges()->where('status', Change::STATUS_PENDING)->exists()) {
                throw new RuntimeException('يوجد طلب تغيير بانتظار قرار الإدارة');
            }
            if ($kind === 'client') {
                if ((int) $user->id !== (int) $contract->client_id) {
                    throw new RuntimeException('غير مسموح');
                }
                if (! $contract->stages()->lateForChange()->exists()) {
                    throw new RuntimeException('يحق طلب تغيير المستقل بعد ' . ContractPolicy::number('late_change_days') . ' أيام تأخير، أو بفتح خلاف تحسمه الإدارة.');
                }
            } elseif ($kind === 'withdrawal' && (int) $user->id !== (int) $contract->freelancer_id) {
                throw new RuntimeException('غير مسموح');
            }
            $change = $contract->freelancerChanges()->create(['requested_by' => $user->id, 'kind' => $kind, 'reason' => $reason, 'leaving_freelancer_id' => $contract->freelancer_id]);
            $this->log($contract, Change::KINDS[$kind] . ' لتغيير المستقل: ' . $reason);

            return $change;
        });

        $contract = $change->contract;
        $this->notifyAdmins(Msg::t('disputes.admin_change_requested', ['number' => $contract->number, 'kind' => Change::KINDS[$kind], 'reason' => $reason]), $contract);
        $other = $kind === 'client' ? $contract->freelancer : $contract->client;
        $this->notify($other, Msg::t('disputes.change_requested', ['number' => $contract->number, 'kind' => Change::KINDS[$kind]]), $contract);

        return $change;
    }

    public function reject(Change $change, User $admin, string $note): void
    {
        DB::transaction(function () use ($change, $admin, $note) {
            $change = Change::whereKey($change->id)->lockForUpdate()->first();
            if ($change->status !== Change::STATUS_PENDING) {
                throw new RuntimeException('هذا الطلب ليس بانتظار قرار');
            }
            $change->update(['status' => Change::STATUS_REJECTED, 'decision_note' => $note, 'decided_by' => $admin->id, 'decided_at' => now()]);
            $this->log($change->contract, 'رفضت الإدارة تغيير المستقل: ' . $note);
        });
        $contract = $change->contract;
        foreach ([$contract->client, $contract->freelancer] as $user) {
            $this->notify($user, Msg::t('disputes.change_rejected', ['number' => $contract->number, 'note' => $note]), $contract);
        }
    }

    /** The admin approves: settle the money, close this contract, publish the rest for new offers. */
    public function apply(Change $change, User $admin, int $percent, string $note): ServiceRequest
    {
        if ($percent < 0 || $percent > 100) {
            throw new RuntimeException('النسبة بين 0 و100');
        }

        [$contract, $continuation] = DB::transaction(function () use ($change, $admin, $percent, $note) {
            $change = Change::whereKey($change->id)->lockForUpdate()->first();
            $contract = Contract::whereKey($change->contract_id)->lockForUpdate()->first();
            if ($change->status !== Change::STATUS_PENDING || $contract->status !== Contract::STATUS_ACTIVE) {
                throw new RuntimeException('لا يمكن تنفيذ هذا التغيير الآن');
            }

            // 1. The current unapproved stage: the leaving freelancer's share, the rest back to the client.
            $current = $this->workflow->currentStage($contract);
            if ($current && $current->status === ContractStage::STATUS_FUNDED) {
                $reason = 'تغيير المستقل: ' . $note;
                $current->deliveries()->where('status', ContractDelivery::STATUS_SUBMITTED)->update(['status' => ContractDelivery::STATUS_REVISION, 'reviewed_at' => now()]);
                match (true) {
                    $percent >= 100 => $this->ledger->release($current, $reason),
                    $percent <= 0 => $this->ledger->refund($current, $reason),
                    default => $this->ledger->split($current, round((float) $current->amount * $percent / 100, 2), $reason),
                };
            }
            // 2. Stages funded ahead go back to the client's balance; open checkouts stop holding stages.
            foreach ($contract->stages()->where('status', ContractStage::STATUS_FUNDED)->get() as $stage) {
                $this->ledger->refund($stage, 'تغيير المستقل: مرحلة مموّلة مقدمًا');
            }
            $contract->stages()->where('status', ContractStage::STATUS_FUNDING)->update(['status' => ContractStage::STATUS_UNFUNDED, 'online_payment_id' => null]);
            $contract->disputes()->whereIn('status', \App\Models\ContractDispute::OPEN_STATUSES)->update(['status' => \App\Models\ContractDispute::STATUS_DECIDED, 'decision' => 'change_freelancer', 'decision_note' => $note, 'decided_by' => $admin->id, 'decided_at' => now()]);

            // 3. The remaining work as a new staged request (same service and answers, what was done, the files).
            $continuation = $this->continuation($contract->fresh(), $note);
            $contract->update(['status' => Contract::STATUS_REPLACED]);
            if ($request = $contract->request) {
                $request->statusChangeNote = 'تغيّر المستقل؛ يُستكمل في ' . $continuation->number;
                $request->update(['status' => ServiceRequest::STATUS_NOT_COMPLETED, 'closed_reason' => 'freelancer_replaced', 'closed_at' => now(), 'closed_by' => $admin->id]);
            }
            $change->update([
                'status' => Change::STATUS_APPROVED, 'leaving_percent' => $percent, 'decision_note' => $note,
                'decided_by' => $admin->id, 'decided_at' => now(), 'continuation_request_id' => $continuation->id,
            ]);
            $this->log($contract, 'اعتمدت الإدارة تغيير المستقل (' . $percent . '% من المرحلة الجارية للمستقل المغادر): ' . $note . ' — يُستكمل في ' . $continuation->number);

            return [$contract, $continuation];
        });

        $this->notify($contract->client, Msg::t('disputes.change_applied_client', ['number' => $contract->number, 'continuation' => $continuation->number]), $contract, '/my/requests/' . $continuation->number);
        $this->notify($contract->freelancer, Msg::t('disputes.change_applied_freelancer', ['number' => $contract->number, 'note' => $note]), $contract);

        return $continuation;
    }

    private function continuation(Contract $contract, string $note): ServiceRequest
    {
        $old = $contract->request;
        $done = $contract->stages()->where('status', ContractStage::STATUS_RELEASED)->pluck('title');
        $left = $contract->stages()->whereNotIn('status', [ContractStage::STATUS_RELEASED])->get()
            ->map(fn ($s) => $s->title . ' (' . $s->deliverable . ')');
        $summary = 'استكمال المشروع ' . $contract->number . ' بعد تغيير المستقل.'
            . ($done->isNotEmpty() ? "\nأُنجز واعتُمد: " . $done->implode('، ') . '.' : '')
            . ($left->isNotEmpty() ? "\nالمتبقي: " . $left->implode('، ') . '.' : '');

        $new = ServiceRequest::create([
            'user_id' => $contract->client_id,
            'service_form_id' => $old?->service_form_id,
            'category_id' => $old?->category_id,
            'sub_category_id' => $old?->sub_category_id,
            'category_name' => $old?->category_name,
            'service_name' => $old?->service_name ?? $contract->title,
            'path' => ServiceRequest::PATH_EXECUTE,
            'status' => ServiceRequest::STATUS_OPEN,
            'kind' => ServiceRequest::KIND_STAGED,
            'project_title' => $contract->title . ' (استكمال)',
            'problem' => $old?->problem ?? $contract->title,
            'stopped_project' => true,
            'done_before' => $summary,
            'contact_name' => $old?->contact_name ?? $contract->client?->name,
            'contact_phone' => $old?->contact_phone ?? (string) $contract->client?->phone,
            'contact_email' => $old?->contact_email ?? $contract->client?->email,
            'contact_method' => $old?->contact_method,
            'published_at' => now(),
            'continues_contract_id' => $contract->id,
        ] + ($old ? $old->only(\App\Services\ServiceRequests\RequestSubmission::FILTER_COLUMNS) : []));
        $new->update(['number' => 'REQ-' . (1000 + $new->id)]);

        foreach ($old?->answers ?? [] as $answer) {
            $new->answers()->create($answer->only(['question_id', 'section_title', 'label', 'type', 'value', 'sort_order']));
        }
        // The original files and everything delivered so far go with the remaining work.
        $files = collect($old?->files ?? [])->map(fn ($f) => [ServiceRequestFile::DISK, $f->path, $f->original_name, $f->mime, $f->size, $f->question_id])
            ->merge(ContractDeliveryFile::whereIn('contract_delivery_id', ContractDelivery::whereIn('contract_stage_id', $contract->stages()->select('id'))->select('id'))->get()
                ->map(fn ($f) => [ContractDeliveryFile::DISK, $f->path, $f->original_name, $f->mime, $f->size, null]));
        foreach ($files as [$disk, $path, $name, $mime, $size, $questionId]) {
            if (! Storage::disk($disk)->exists($path)) {
                continue;
            }
            $copy = 'service-requests/' . $new->id . '/' . basename($path);
            Storage::disk(ServiceRequestFile::DISK)->put($copy, Storage::disk($disk)->get($path));
            $new->files()->create(['question_id' => $questionId, 'original_name' => $name, 'path' => $copy, 'mime' => $mime, 'size' => $size]);
        }

        return $new;
    }

    private function log(Contract $contract, string $note): void
    {
        if ($contract->request) {
            ServiceRequestEvent::record($contract->request, ServiceRequestEvent::TYPE_AGREEMENT, ['note' => $note]);
        }
    }

    private function notify(?User $user, string $text, Contract $contract, ?string $path = null): void
    {
        DB::afterCommit(fn () => $user?->notify(new RequestUpdateNotification($text, $path ?? '/contracts/' . $contract->number, 'contracts')));
    }

    private function notifyAdmins(string $text, Contract $contract): void
    {
        DB::afterCommit(fn () => Notification::send(User::activeAdmins()->get(), new RequestUpdateNotification($text, '/contracts/' . $contract->number, 'admin_alerts')));
    }
}
