<?php

namespace App\Services\Contracts;

use App\Models\Contract;
use App\Models\ContractDelivery;
use App\Models\ContractDeliveryFile;
use App\Models\ContractPause;
use App\Models\ContractStage;
use App\Models\ServiceRequestEvent;
use App\Models\User;
use App\Notifications\RequestUpdateNotification;
use App\Support\Msg;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

/**
 * Service 3, phase 4 (client decisions 2026-10-16). Pauses: the freelancer asks the client for
 * something specific and the stage clock stops; it resumes when the client answers (or the
 * freelancer resumes it) and the deadline moves by the paused time. The client may object to a
 * request that is not real: the pause ends without an extension and the admin looks at it.
 * `tick()` (command `contracts:deadlines`) sends the reminders and escalations, once each.
 */
class StageClock
{
    public function pause(ContractStage $stage, User $freelancer, string $request): ContractPause
    {
        $pause = DB::transaction(function () use ($stage, $freelancer, $request) {
            $stage = ContractStage::whereKey($stage->id)->lockForUpdate()->first();
            $contract = $stage->contract;
            if ((int) $contract->freelancer_id !== (int) $freelancer->id || $contract->status !== Contract::STATUS_ACTIVE
                || $stage->status !== ContractStage::STATUS_FUNDED || ! $stage->started_at) {
                throw new RuntimeException('يمكن إيقاف العدّاد للمرحلة الحالية الجارية فقط');
            }
            if ($stage->activePause()) {
                throw new RuntimeException('العدّاد متوقف بالفعل بانتظار العميل');
            }
            if ($contract->openDispute()) {
                throw new RuntimeException('على المشروع خلاف مفتوح؛ تنتظر المرحلة قرار الإدارة');
            }
            if ($stage->deliveries()->where('status', ContractDelivery::STATUS_SUBMITTED)->exists()) {
                throw new RuntimeException('تسليمك بانتظار مراجعة العميل؛ العدّاد لا يُحسب عليك الآن');
            }
            $pause = $stage->pauses()->create(['requested_by' => $freelancer->id, 'request' => $request, 'status' => ContractPause::STATUS_ACTIVE, 'started_at' => now()]);
            $this->log($contract, 'أوقف المستقل العدّاد على المرحلة «' . $stage->title . '» بانتظار العميل: ' . $request);

            return $pause;
        });

        $contract = $pause->stage->contract;
        $this->notify($contract->client, Msg::t('clock.pause_requested', ['number' => $contract->number, 'request' => $request, 'days' => ContractPolicy::number('pause_max_days')]), $contract);

        return $pause;
    }

    /** @param  UploadedFile[]  $files  The client sends what was asked for: the clock resumes. */
    public function answer(ContractPause $pause, User $client, string $response, array $files = []): void
    {
        $paths = [];
        try {
            DB::transaction(function () use ($pause, $client, $response, $files, &$paths) {
                $pause = $this->activeFor($pause, fn (Contract $c) => (int) $c->client_id === (int) $client->id);
                foreach ($files as $file) {
                    $path = $file->store('contracts/' . $pause->stage->contract_id . '/pause-' . $pause->id, ContractDeliveryFile::DISK);
                    $paths[] = $path;
                    $pause->files()->create(['original_name' => mb_substr($file->getClientOriginalName(), 0, 255), 'path' => $path, 'mime' => $file->getClientMimeType(), 'size' => $file->getSize()]);
                }
                $this->resume($pause, ['client_response' => $response], 'أرسل العميل المطلوب واستؤنف العدّاد');
            });
        } catch (\Throwable $e) {
            Storage::disk(ContractDeliveryFile::DISK)->delete($paths);
            throw $e;
        }
        $contract = $pause->stage->contract;
        $this->notify($contract->freelancer, Msg::t('clock.pause_answered', ['number' => $contract->number, 'date' => $pause->stage->fresh()->due_at->format('Y-m-d')]), $contract);
    }

    /** The freelancer got what was needed some other way (e.g. in the chat). */
    public function resumeByFreelancer(ContractPause $pause, User $freelancer): void
    {
        DB::transaction(function () use ($pause, $freelancer) {
            $pause = $this->activeFor($pause, fn (Contract $c) => (int) $c->freelancer_id === (int) $freelancer->id);
            $this->resume($pause, [], 'استأنف المستقل العدّاد');
        });
    }

    /** A delivery ends a running pause (inside the delivery transaction, stage locked). */
    public function endOnDelivery(ContractStage $stage): void
    {
        $pause = ContractPause::where('contract_stage_id', $stage->id)->where('status', ContractPause::STATUS_ACTIVE)->lockForUpdate()->first();
        if ($pause) {
            $this->resume($pause, [], 'سلّم المستقل أثناء إيقاف العدّاد');
        }
    }

    /** The client says the request is not real: no extension, the admin decides. */
    public function object(ContractPause $pause, User $client, string $reason): void
    {
        DB::transaction(function () use ($pause, $client, $reason) {
            $pause = $this->activeFor($pause, fn (Contract $c) => (int) $c->client_id === (int) $client->id);
            $pause->update(['status' => ContractPause::STATUS_OBJECTED, 'objection' => $reason, 'ended_at' => now()]);
            $this->log($pause->stage->contract, 'اعترض العميل على إيقاف العدّاد (دون تمديد الموعد): ' . $reason);
        });
        $contract = $pause->stage->contract;
        $this->notify($contract->freelancer, Msg::t('clock.pause_objected', ['number' => $contract->number, 'reason' => $reason]), $contract);
        $this->notifyAdmins(Msg::t('clock.admin_pause_objected', ['number' => $contract->number, 'reason' => $reason]), $contract);
    }

    /** Reminders and escalations, each sent once (runs every 15 minutes). */
    public function tick(): array
    {
        $done = ['due_soon' => 0, 'late' => 0, 'late_7' => 0, 'review_reminder' => 0, 'review_admin' => 0, 'pause_reminder' => 0, 'pause_admin' => 0, 'rating_reminder' => 0];

        // Ratings are optional: one reminder two days after completion, to whoever has not rated.
        foreach (Contract::where('status', Contract::STATUS_COMPLETED)->whereNull('rating_reminder_sent_at')->where('completed_at', '<=', now()->subDays(ContractPolicy::number('rating_reminder_days')))->with('ratings')->get() as $contract) {
            $contract->update(['rating_reminder_sent_at' => now()]);
            foreach ([$contract->client, $contract->freelancer] as $user) {
                if ($user && ! $contract->ratings->contains('rater_id', $user->id)) {
                    $this->notify($user, Msg::t('clock.rating_reminder', ['number' => $contract->number]), $contract);
                }
            }
            $done['rating_reminder']++;
        }
        $running = ContractStage::where('status', ContractStage::STATUS_FUNDED)->whereNotNull('started_at')
            ->whereHas('contract', fn ($q) => $q->where('status', Contract::STATUS_ACTIVE))->with('contract')->get();

        foreach ($running as $stage) {
            $waiting = $stage->activePause() || $stage->deliveries()->where('status', ContractDelivery::STATUS_SUBMITTED)->exists();
            if ($waiting) {
                continue; // the clock is not running against the freelancer
            }
            $contract = $stage->contract;
            if (! $stage->due_reminder_sent_at && $stage->due_at->isFuture() && $stage->due_at->lte(now()->addDays(ContractPolicy::number('due_reminder_days')))) {
                $stage->update(['due_reminder_sent_at' => now()]);
                $this->notify($contract->freelancer, Msg::t('clock.due_reminder', ['number' => $contract->number, 'stage' => $stage->title, 'date' => $stage->due_at->format('Y-m-d H:i')]), $contract);
                $done['due_soon']++;
            }
            if (! $stage->late_notified_at && $stage->due_at->isPast()) {
                $stage->update(['late_notified_at' => now()]);
                $this->log($contract, 'تأخرت المرحلة «' . $stage->title . '» عن موعدها ' . $stage->due_at->format('Y-m-d'));
                $this->notify($contract->freelancer, Msg::t('clock.late_freelancer', ['number' => $contract->number, 'stage' => $stage->title]), $contract);
                $this->notify($contract->client, Msg::t('clock.late_client', ['number' => $contract->number, 'stage' => $stage->title, 'days' => ContractPolicy::number('late_change_days')]), $contract);
                $this->notifyAdmins(Msg::t('clock.admin_late', ['number' => $contract->number, 'stage' => $stage->title]), $contract);
                $done['late']++;
            }
            if (! $stage->late_escalated_at && $stage->due_at->lte(now()->subDays(ContractPolicy::number('late_change_days')))) {
                $stage->update(['late_escalated_at' => now()]);
                $this->log($contract, 'مرّت ' . ContractPolicy::number('late_change_days') . ' أيام على تأخر المرحلة «' . $stage->title . '»؛ يحق للعميل طلب تغيير المستقل');
                $this->notify($contract->client, Msg::t('clock.late_escalated_client', ['number' => $contract->number, 'stage' => $stage->title, 'days' => ContractPolicy::number('late_change_days')]), $contract);
                $this->notifyAdmins(Msg::t('clock.admin_late_escalated', ['number' => $contract->number, 'stage' => $stage->title, 'days' => ContractPolicy::number('late_change_days')]), $contract);
                $done['late_7']++;
            }
        }

        $reviews = ContractDelivery::where('status', ContractDelivery::STATUS_SUBMITTED)->with('stage.contract')->get();
        foreach ($reviews as $delivery) {
            $contract = $delivery->stage->contract;
            if (! $delivery->reminder_sent_at && $delivery->created_at->lte(now()->subDays(ContractPolicy::number('review_reminder_days')))) {
                $delivery->update(['reminder_sent_at' => now()]);
                $this->notify($contract->client, Msg::t('clock.review_reminder', ['number' => $contract->number, 'stage' => $delivery->stage->title, 'date' => $delivery->review_due_at->format('Y-m-d')]), $contract);
                $done['review_reminder']++;
            }
            if (! $delivery->escalated_at && $delivery->review_due_at->isPast()) {
                $delivery->update(['escalated_at' => now()]);
                $this->log($contract, 'انتهت مهلة مراجعة العميل لتسليم المرحلة «' . $delivery->stage->title . '»؛ أُحيل للإدارة (لا اعتماد تلقائي)');
                $this->notifyAdmins(Msg::t('clock.admin_review_overdue', ['number' => $contract->number, 'stage' => $delivery->stage->title]), $contract);
                $done['review_admin']++;
            }
        }

        foreach (ContractPause::where('status', ContractPause::STATUS_ACTIVE)->with('stage.contract')->get() as $pause) {
            $contract = $pause->stage->contract;
            if (! $pause->reminder_sent_at && $pause->started_at->lte(now()->subDays(ContractPolicy::number('pause_reminder_days')))) {
                $pause->update(['reminder_sent_at' => now()]);
                $this->notify($contract->client, Msg::t('clock.pause_reminder', ['number' => $contract->number, 'request' => $pause->request]), $contract);
                $done['pause_reminder']++;
            }
            if (! $pause->escalated_at && $pause->started_at->lte(now()->subDays(ContractPolicy::number('pause_max_days')))) {
                $pause->update(['escalated_at' => now()]);
                $this->log($contract, 'مرّت ' . ContractPolicy::number('pause_max_days') . ' أيام على إيقاف العدّاد دون رد العميل؛ أُحيل للإدارة');
                $this->notifyAdmins(Msg::t('clock.admin_pause_escalated', ['number' => $contract->number, 'days' => ContractPolicy::number('pause_max_days')]), $contract);
                $done['pause_admin']++;
            }
        }

        return $done;
    }

    private function resume(ContractPause $pause, array $attributes, string $logNote): void
    {
        $minutes = (int) round($pause->started_at->diffInSeconds(now()) / 60);
        $pause->update($attributes + ['status' => ContractPause::STATUS_RESUMED, 'ended_at' => now(), 'extended_minutes' => $minutes]);
        $stage = ContractStage::whereKey($pause->contract_stage_id)->lockForUpdate()->first();
        // The deadline moves by the paused time; reminders about it may be sent again.
        $due = $stage->due_at->copy()->addMinutes($minutes);
        // Lateness alerts and the right to change the freelancer start over only if the stage is on time again.
        $stage->update(['due_at' => $due, 'due_reminder_sent_at' => null] + ($due->isFuture() ? ['late_notified_at' => null, 'late_escalated_at' => null] : []));
        $this->log($stage->contract, $logNote . '؛ موعد المرحلة «' . $stage->title . '» صار ' . $stage->due_at->format('Y-m-d H:i'));
    }

    private function activeFor(ContractPause $pause, callable $allowed): ContractPause
    {
        $pause = ContractPause::whereKey($pause->id)->lockForUpdate()->first();
        if ($pause->status !== ContractPause::STATUS_ACTIVE || ! $allowed($pause->stage->contract)) {
            throw new RuntimeException('هذا الإيقاف لم يعد قائمًا');
        }

        return $pause;
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

    private function notifyAdmins(string $text, Contract $contract): void
    {
        DB::afterCommit(fn () => Notification::send(User::activeAdmins()->get(), new RequestUpdateNotification($text, '/contracts/' . $contract->number, 'admin_alerts')));
    }
}
