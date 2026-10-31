<?php

namespace App\Services\Business;

use App\Models\BusinessCompany;
use App\Models\BusinessDelivery;
use App\Models\BusinessDeliveryFile;
use App\Models\BusinessEvent;
use App\Models\BusinessProject;
use App\Models\BusinessRequest;
use App\Models\BusinessRequestFile;
use App\Models\BusinessTask;
use App\Models\User;
use App\Notifications\BusinessNotification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * «نفّذها للأعمال» (client file «قسم الشركات», phase 1): every action on a company request or project, each one
 * logged in business_events (who, when, what) and notified. The Nafizha team executes; a request becomes a project
 * only after its quote is accepted (by the client in his account, or by the admin for him).
 */
class BusinessService
{
    // ── Requests ───────────────────────────────────────────────────────

    /**
     * Saves a request once per form (`submission_key`; a resend of the same form by the same sender returns the
     * first one). $files = uploaded files, each [question_id|null, UploadedFile].
     *
     * @return array{0: BusinessRequest, 1: bool} the request and whether it was created now
     */
    public function submit(array $data, ?User $user, ?string $ip, array $files = []): array
    {
        if ($existing = $this->alreadySent($data['submission_key'], $user, $ip)) {
            return [$existing, false];
        }
        if (BusinessRequest::where('submission_key', $data['submission_key'])->exists()) {
            $data['submission_key'] = (string) Str::uuid(); // someone else's key: a new request of its own
        }

        $paths = [];
        try {
            $request = DB::transaction(function () use ($data, $user, $ip, $files, &$paths) {
                $request = BusinessRequest::create([
                    'submission_key' => $data['submission_key'],
                    'source' => $data['source'],
                    'user_id' => $user?->id,
                    'contact_name' => $data['contact_name'] ?? $user?->name,
                    'phone' => $data['phone'],
                    'email' => $data['email'] ?? $user?->email,
                    'company_name' => $data['company_name'] ?? null,
                    'company_size' => $data['company_size'] ?? null,
                    'details' => $data['details'] ?? null,
                    'answers' => $data['answers'] ?? null,
                    'status' => BusinessRequest::STATUS_NEW,
                    'consent_at' => now(),
                    'ip' => $ip,
                ]);
                $request->update(['number' => 'BIZ-' . (1000 + $request->id)]);
                foreach ($files as [$questionId, $file]) {
                    $paths[] = $this->storeRequestFile($request, $file, $questionId);
                }
                if ($user && ! empty($data['company_name'])) {
                    BusinessCompany::updateOrCreate(['user_id' => $user->id], array_filter([
                        'name' => $data['company_name'], 'size' => $data['company_size'] ?? null, 'phone' => $data['phone'],
                    ], fn ($v) => $v !== null));
                }
                $this->log($request, 'created', $request->sourceLabel(), $user);

                return $request;
            });
        } catch (QueryException $e) {
            Storage::disk(BusinessRequestFile::DISK)->delete(array_filter($paths));
            if ($existing = $this->alreadySent($data['submission_key'], $user, $ip)) {
                return [$existing, false];
            }
            throw $e;
        } catch (\Throwable $e) {
            Storage::disk(BusinessRequestFile::DISK)->delete(array_filter($paths));
            throw $e;
        }

        $this->notifyTeam('business.admin_new', $request, ['source' => $request->sourceLabel(), 'name' => $request->displayName()], "/admin/business/requests/{$request->number}");
        $this->notifyClient($request->user, 'business.received', $request, [], route('business.requests.show', $request, false));

        return [$request, true];
    }

    public function alreadySent(string $key, ?User $user, ?string $ip): ?BusinessRequest
    {
        return BusinessRequest::where('submission_key', $key)->where('user_id', $user?->id)
            ->when(! $user, fn ($q) => $q->where('ip', $ip))->first();
    }

    public function setStatus(BusinessRequest $request, string $status, ?string $note, User $by): void
    {
        if ($request->status === $status && ! filled($note)) {
            return;
        }
        $from = $request->statusLabel();
        $request->update(['status' => $status]);
        $this->log($request, 'status', $from . ' ← ' . $request->statusLabel() . (filled($note) ? ' — ' . trim($note) : ''), $by);
    }

    public function assign(BusinessRequest $request, ?User $assignee, User $by): void
    {
        if ((int) $request->assigned_to === (int) $assignee?->id) {
            return;
        }
        $request->update(['assigned_to' => $assignee?->id]);
        $this->log($request, 'assigned', $assignee?->name ?? 'بدون مسؤول', $by);
        if ($assignee && $assignee->id !== $by->id) {
            $this->send($assignee, new BusinessNotification('business.admin_assigned', $this->vars($request), "/admin/business/requests/{$request->number}", 'business_admin'));
        }
    }

    public function note(Model $subject, string $body, User $by): void
    {
        $this->log($subject, 'note', trim($body), $by, internal: true);
    }

    /** The team's price for this request (a new quote replaces the previous one; both stay in the log). */
    public function sendQuote(BusinessRequest $request, float $amount, string $scope, ?string $dueAt, User $by): void
    {
        $request->update([
            'quote_amount' => $amount, 'quote_scope' => trim($scope), 'quote_due_at' => $dueAt,
            'quote_sent_at' => now(), 'quote_decided_at' => null, 'decision_note' => null,
            'status' => BusinessRequest::STATUS_QUOTED,
        ]);
        $this->log($request, 'quote', \App\Support\Money::format($amount) . ' — ' . Str::limit(trim($scope), 300), $by);
        $this->notifyClient($request->user, 'business.quote_sent', $request, ['amount' => \App\Support\Money::format($amount)], route('business.requests.show', $request, false));
    }

    /** Accepted or rejected — by the client in his account, or by the admin after a call. */
    public function decideQuote(BusinessRequest $request, bool $accept, ?string $note, User $by): void
    {
        if (! $request->hasQuote() || $request->status === BusinessRequest::STATUS_CONVERTED) {
            throw new \RuntimeException('لا يوجد عرض سعر بانتظار القرار');
        }
        $request->update([
            'status' => $accept ? BusinessRequest::STATUS_ACCEPTED : BusinessRequest::STATUS_REJECTED,
            'quote_decided_at' => now(), 'decision_note' => filled($note) ? trim($note) : null,
        ]);
        $byClient = (int) $by->id === (int) $request->user_id;
        $this->log($request, $accept ? 'quote_accepted' : 'quote_rejected', ($byClient ? 'بواسطة العميل' : 'سجّله الفريق') . (filled($note) ? ' — ' . trim($note) : ''), $by);
        if ($byClient) {
            $this->notifyTeam($accept ? 'business.admin_quote_accepted' : 'business.admin_quote_rejected', $request, ['name' => $request->displayName()], "/admin/business/requests/{$request->number}");
        }
    }

    /** Payment received outside the site (cash, transfer…) or confirmed online (method «online»). */
    public function markPaid(BusinessRequest $request, string $method, ?string $reference, ?User $by): void
    {
        if ($request->isPaid()) {
            return;
        }
        $request->update(['payment_status' => 'paid', 'payment_method' => $method, 'payment_reference' => $reference, 'paid_at' => now()]);
        $this->log($request, 'paid', $request->paymentMethodLabel() . ($reference ? ' — ' . $reference : '') . ' — ' . \App\Support\Money::format($request->quote_amount ?? 0), $by);
        if (! $by || (int) $by->id === (int) $request->user_id) {
            $this->notifyTeam('business.admin_paid', $request, ['name' => $request->displayName()], "/admin/business/requests/{$request->number}");
        }
        $this->notifyClient($request->user, 'business.paid', $request, [], route('business.requests.show', $request, false));
    }

    /**
     * A visitor's request (no account) is linked to a client account before it becomes a project: an existing
     * client account with this email, or a new one (the client sets his password from the emailed link).
     */
    public function linkAccount(BusinessRequest $request, string $email, ?string $name, User $by): User
    {
        $email = Str::lower(trim($email));
        $user = User::where('email', $email)->first();
        if ($user && $user->account_type !== User::TYPE_CLIENT) {
            throw new \RuntimeException('هذا البريد مسجّل لحساب ليس حساب عميل');
        }
        $created = false;
        if (! $user) {
            $user = new User();
            $user->forceFill([
                'name' => $name ?: ($request->contact_name ?: $request->displayName()),
                'user_name' => 'biz' . Str::lower(Str::random(8)),
                'email' => $email,
                'phone' => $request->phone,
                'password' => Hash::make(Str::random(40)),
                'account_type' => User::TYPE_CLIENT,
                'status' => 1,
                'email_verified_at' => now(),
            ])->save();
            $created = true;
        }
        DB::transaction(function () use ($request, $user) {
            $request->update(['user_id' => $user->id, 'email' => $user->email]);
            if ($request->company_name) {
                BusinessCompany::firstOrCreate(['user_id' => $user->id], ['name' => $request->company_name, 'size' => $request->company_size, 'phone' => $request->phone]);
            }
        });
        $this->log($request, 'account', ($created ? 'أُنشئ حساب عميل ' : 'رُبط بحساب ') . $user->email, $by);
        if ($created) {
            $this->sendPasswordLink($user);
        }

        return $user;
    }

    /** An accepted request becomes a project (the request stays, linked, with its whole history). */
    public function convert(BusinessRequest $request, string $title, ?User $manager, ?string $dueAt, User $by): BusinessProject
    {
        if ($request->status !== BusinessRequest::STATUS_ACCEPTED) {
            throw new \RuntimeException('يتحوّل الطلب لمشروع بعد قبول عرض السعر');
        }
        if (! $request->user_id) {
            throw new \RuntimeException('اربط الطلب بحساب العميل أولًا (من «حساب العميل») ليتابع مشروعه');
        }

        $project = DB::transaction(function () use ($request, $title, $manager, $dueAt, $by) {
            $project = BusinessProject::create([
                'user_id' => $request->user_id, 'manager_id' => $manager?->id, 'title' => trim($title),
                'description' => $request->quote_scope, 'amount' => $request->quote_amount ?? 0, 'due_at' => $dueAt,
                'status' => BusinessProject::STATUS_ACTIVE,
            ]);
            $project->update(['number' => 'BPR-' . (1000 + $project->id)]);
            $request->update(['status' => BusinessRequest::STATUS_CONVERTED, 'business_project_id' => $project->id]);
            $this->log($request, 'converted', $project->number, $by);
            $this->log($project, 'created', 'من الطلب ' . $request->number, $by);

            return $project;
        });
        $this->notifyClient($request->user, 'business.project_created', $request, ['project' => $project->number, 'title' => $project->title], route('business.projects.show', $project, false));

        return $project;
    }

    // ── Projects ───────────────────────────────────────────────────────

    public function saveTask(BusinessProject $project, array $data, User $by, ?BusinessTask $task = null): BusinessTask
    {
        $data['completed_at'] = ($data['status'] ?? null) === BusinessTask::STATUS_DONE ? ($task?->completed_at ?? now()) : null;
        if ($task) {
            $before = $task->statusLabel();
            $task->update($data);
            $this->log($project, 'task', '«' . $task->title . '» ' . ($before !== $task->statusLabel() ? $before . ' ← ' . $task->statusLabel() : 'عُدّلت'), $by);
        } else {
            $task = $project->tasks()->create($data + ['sort_order' => (int) $project->tasks()->max('sort_order') + 1]);
            $this->log($project, 'task', 'مهمة جديدة «' . $task->title . '»', $by);
        }
        $newAssignee = $task->wasRecentlyCreated ? (bool) $task->assignee_id : $task->wasChanged('assignee_id');
        if ($newAssignee && $task->assignee && $task->assignee_id !== $by->id) {
            $this->send($task->assignee, new BusinessNotification('business.admin_task_assigned', ['project' => $project->number, 'title' => $task->title],
                "/admin/business/projects/{$project->number}", 'business_admin'));
        }

        return $task;
    }

    public function deleteTask(BusinessTask $task, User $by): void
    {
        $this->log($task->project, 'task', 'حُذفت المهمة «' . $task->title . '»', $by);
        $task->delete();
    }

    /**
     * A new version for the client's approval. With $previous it continues that deliverable (same root, version + 1);
     * its task goes to «للمراجعة».
     */
    public function deliver(BusinessProject $project, array $data, array $files, User $by, ?BusinessDelivery $previous = null): BusinessDelivery
    {
        $paths = [];
        try {
            $delivery = DB::transaction(function () use ($project, $data, $files, $by, $previous, &$paths) {
                $rootId = $previous?->rootId();
                $delivery = $project->deliveries()->create([
                    'business_task_id' => $data['business_task_id'] ?? $previous?->business_task_id,
                    'root_id' => $rootId,
                    'version' => $rootId ? (int) BusinessDelivery::where('id', $rootId)->orWhere('root_id', $rootId)->max('version') + 1 : 1,
                    'title' => $previous?->title ?? $data['title'],
                    'note' => $data['note'] ?? null,
                    'links' => $data['links'] ?: null,
                    'status' => BusinessDelivery::STATUS_PENDING,
                    'submitted_by' => $by->id,
                ]);
                foreach ($files as $file) {
                    $path = $file->store('business/projects/' . $project->id, BusinessDeliveryFile::DISK);
                    $paths[] = $path;
                    $delivery->files()->create(['original_name' => mb_substr($file->getClientOriginalName(), 0, 255), 'path' => $path,
                        'mime' => $file->getClientMimeType(), 'size' => $file->getSize()]);
                }
                $delivery->task?->update(['status' => BusinessTask::STATUS_REVIEW]);
                $this->log($project, 'delivery', '«' . $delivery->title . '» نسخة ' . $delivery->version, $by);

                return $delivery;
            });
        } catch (\Throwable $e) {
            Storage::disk(BusinessDeliveryFile::DISK)->delete($paths);
            throw $e;
        }
        $this->notifyClient($project->user, 'business.delivery_ready', $project, ['title' => $delivery->title, 'version' => $delivery->version],
            route('business.projects.show', $project, false) . '#delivery-' . $delivery->id);

        return $delivery;
    }

    /** The client's approval gate: approve this version, or ask for changes (comment required). */
    public function review(BusinessDelivery $delivery, bool $approve, ?string $comment, User $by): void
    {
        if ($delivery->status !== BusinessDelivery::STATUS_PENDING) {
            throw new \RuntimeException('تمت مراجعة هذه النسخة بالفعل');
        }
        if (! $approve && ! filled($comment)) {
            throw new \RuntimeException('اكتب التعديل المطلوب');
        }
        $project = $delivery->project;
        DB::transaction(function () use ($delivery, $approve, $comment, $by, $project) {
            $delivery->update(['status' => $approve ? BusinessDelivery::STATUS_APPROVED : BusinessDelivery::STATUS_CHANGES,
                'client_comment' => filled($comment) ? trim($comment) : null, 'reviewed_by' => $by->id, 'reviewed_at' => now()]);
            $delivery->task?->update($approve
                ? ['status' => BusinessTask::STATUS_DONE, 'completed_at' => now()]
                : ['status' => BusinessTask::STATUS_IN_PROGRESS, 'completed_at' => null]);
            $this->log($project, $approve ? 'approved' : 'changes', '«' . $delivery->title . '» نسخة ' . $delivery->version . (filled($comment) ? ' — ' . trim($comment) : ''), $by);
        });
        $vars = ['project' => $project->number, 'title' => $delivery->title, 'version' => $delivery->version, 'name' => $by->name];
        $path = "/admin/business/projects/{$project->number}";
        $team = $project->manager ? collect([$project->manager]) : $this->teamMembers();
        $this->send($team, new BusinessNotification($approve ? 'business.admin_approved' : 'business.admin_changes', $vars, $path, 'business_admin'));
    }

    public function setProjectStatus(BusinessProject $project, string $status, User $by): void
    {
        if ($project->status === $status) {
            return;
        }
        $project->update(['status' => $status, 'completed_at' => $status === BusinessProject::STATUS_COMPLETED ? now() : null]);
        $this->log($project, 'status', $project->statusLabel(), $by);
        if ($status === BusinessProject::STATUS_COMPLETED) {
            $this->notifyClient($project->user, 'business.project_completed', $project, ['title' => $project->title], route('business.projects.show', $project, false));
        }
    }

    // ── Shared ─────────────────────────────────────────────────────────

    public function log(Model $subject, string $action, ?string $details, ?User $by, bool $internal = false): BusinessEvent
    {
        return BusinessEvent::create(['subject_type' => $subject->getMorphClass(), 'subject_id' => $subject->getKey(),
            'user_id' => $by?->id, 'action' => $action, 'details' => $details, 'internal' => $internal]);
    }

    /** Staff who handle company requests: active admins except the read-only roles. */
    public function teamMembers()
    {
        return User::activeAdmins()->get()->reject(fn (User $u) => in_array(\App\Support\StaffPermissions::role($u), ['analyst', 'content', 'suggestions'], true))->values();
    }

    private function storeRequestFile(BusinessRequest $request, UploadedFile $file, ?int $questionId): string
    {
        $path = $file->store('business/requests/' . $request->id, BusinessRequestFile::DISK);
        $request->files()->create(['question_id' => $questionId, 'original_name' => mb_substr($file->getClientOriginalName(), 0, 255),
            'path' => $path, 'mime' => $file->getClientMimeType(), 'size' => $file->getSize()]);

        return $path;
    }

    private function vars(Model $subject): array
    {
        return $subject instanceof BusinessRequest
            ? ['number' => $subject->number, 'name' => $subject->displayName()]
            : ['number' => $subject->number, 'title' => $subject->title];
    }

    private function notifyTeam(string $key, Model $subject, array $vars, string $path): void
    {
        $this->send($this->teamMembers(), new BusinessNotification($key, $this->vars($subject) + $vars, $path, 'business_admin'));
    }

    private function notifyClient(?User $client, string $key, Model $subject, array $vars, string $path): void
    {
        if ($client) {
            $this->send($client, new BusinessNotification($key, $this->vars($subject) + $vars, $path, 'business'));
        }
    }

    /** Sent once the action is committed; a failing mail server never undoes the action (or a payment). */
    private function send($notifiables, BusinessNotification $notification): void
    {
        DB::afterCommit(function () use ($notifiables, $notification) {
            try {
                Notification::send($notifiables, $notification);
            } catch (\Throwable $e) {
                report($e);
            }
        });
    }

    /** A new client account gets a link to set his password (same token flow as «نسيت كلمة المرور»). */
    private function sendPasswordLink(User $user): void
    {
        try {
            $token = Str::random(64);
            DB::table('password_reset_tokens')->updateOrInsert(['email' => $user->email], ['token' => Hash::make($token), 'created_at' => now()]);
            Mail::send('website.emails.UserChangePasswordMail', ['code' => $token . '|' . base64_encode($user->email)], function ($message) use ($user) {
                $message->to($user->email)->subject('حسابك في نفّذها للأعمال — اضبط كلمة المرور');
            });
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
