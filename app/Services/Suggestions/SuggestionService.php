<?php

namespace App\Services\Suggestions;

use App\Models\Suggestion;
use App\Models\SuggestionCategory;
use App\Models\User;
use App\Notifications\SuggestionAdminNotification;
use App\Notifications\SuggestionUpdateNotification;
use App\Support\StaffPermissions;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;

/**
 * «شاركنا اقتراحك» (client file «نظام الاقتراحات»): saving a suggestion once, the team's actions
 * (status with an optional reason shown to the sender, assignment, priority, duplicate link) and the audit log.
 * Only the receipt and the statuses in Suggestion::NOTIFIED_STATUSES reach the sender; notes never do.
 */
class SuggestionService
{
    /**
     * Saves the suggestion (validated data) and returns it. The form's submission_key makes this
     * idempotent: a double click or a resend after a network error returns the suggestion already saved.
     *
     * @return array{0: Suggestion, 1: bool} the suggestion and whether it was created now
     */
    public function submit(array $data, ?User $user, ?string $ip): array
    {
        if ($existing = $this->alreadySent($data['submission_key'], $user, $ip)) {
            return [$existing, false];
        }
        // The key belongs to someone else's form (leaked or guessed): this is a new suggestion of its own.
        if (Suggestion::where('submission_key', $data['submission_key'])->exists()) {
            $data['submission_key'] = (string) Str::uuid();
        }

        $category = SuggestionCategory::find($data['category_id']);
        try {
            $suggestion = DB::transaction(function () use ($data, $user, $ip, $category) {
                $suggestion = Suggestion::create([
                    'submission_key' => $data['submission_key'],
                    'user_id' => $user?->id,
                    'user_role' => Suggestion::roleOf($user),
                    // The account's identity is taken from the account; a visitor's name/email only from the form.
                    'guest_name' => $user ? null : ($data['guest_name'] ?? null),
                    'guest_email' => $user ? null : (! empty($data['wants_updates']) ? ($data['guest_email'] ?? null) : null),
                    'wants_updates' => $user ? true : ! empty($data['wants_updates']),
                    'category_id' => $category?->id,
                    'category_name' => $category?->name,
                    'title' => $data['title'],
                    'description' => $data['description'],
                    'status' => Suggestion::STATUS_NEW,
                    'ip' => $ip,
                ]);
                $suggestion->update(['reference_code' => 'SUG-' . (1000 + $suggestion->id)]);
                $suggestion->history()->create(['user_id' => null, 'action' => 'created', 'to_status' => Suggestion::STATUS_NEW]);

                return $suggestion;
            });
        } catch (QueryException $e) {
            // Two identical submissions at the same moment: the unique key let only one in.
            if ($existing = $this->alreadySent($data['submission_key'], $user, $ip)) {
                return [$existing, false];
            }
            throw $e;
        }

        Notification::send(StaffPermissions::suggestionStaff('manage')->get(), new SuggestionAdminNotification($suggestion));
        $this->notifySender($suggestion, 'received');

        return [$suggestion, true];
    }

    /** The suggestion this form already saved, for the same sender only (an account, or the same IP for a visitor). */
    public function alreadySent(string $submissionKey, ?User $user, ?string $ip): ?Suggestion
    {
        return Suggestion::where('submission_key', $submissionKey)
            ->where('user_id', $user?->id)
            ->when(! $user, fn ($q) => $q->where('ip', $ip))
            ->first();
    }

    /**
     * Status change by the team; $reason is the reply shown to the sender (e.g. the professional reply when
     * closing). An empty reply keeps the previous one, so a later status change never wipes what was written.
     */
    public function changeStatus(Suggestion $suggestion, string $status, ?string $reason, User $by): void
    {
        $reason = filled($reason) ? trim($reason) : $suggestion->status_reason;
        $from = $suggestion->status;
        if ($from === $status && $reason === $suggestion->status_reason) {
            return;
        }

        $suggestion->update(['status' => $status, 'status_reason' => $reason]);
        $suggestion->history()->create(['user_id' => $by->id, 'action' => 'status', 'from_status' => $from, 'to_status' => $status, 'details' => $reason]);

        if ($from !== $status && in_array($status, Suggestion::NOTIFIED_STATUSES, true)) {
            $this->notifySender($suggestion, $status);
        }
    }

    public function assign(Suggestion $suggestion, ?User $assignee, User $by): void
    {
        if ((int) $suggestion->assigned_admin_id === (int) $assignee?->id) {
            return;
        }
        $suggestion->update(['assigned_admin_id' => $assignee?->id]);
        $suggestion->history()->create(['user_id' => $by->id, 'action' => 'assigned', 'details' => $assignee?->name ?? 'بدون مسؤول']);
        if ($assignee && $assignee->id !== $by->id) {
            $assignee->notify(new SuggestionAdminNotification($suggestion, assigned: true));
        }
    }

    public function setPriority(Suggestion $suggestion, string $priority, User $by): void
    {
        if ($suggestion->internal_priority === $priority) {
            return;
        }
        $suggestion->update(['internal_priority' => $priority]);
        $suggestion->history()->create(['user_id' => $by->id, 'action' => 'priority', 'details' => Suggestion::PRIORITIES[$priority]]);
    }

    /**
     * Links a repeated suggestion to the original (or removes the link with null). Nothing is merged
     * away: both stay as sent, and the original lists its repeats.
     */
    public function linkDuplicate(Suggestion $suggestion, ?Suggestion $original, User $by): void
    {
        if ($original) {
            // Always point at the root, so repeats never form a chain or a loop.
            $original = $original->duplicateOf ?? $original;
            if ($original->id === $suggestion->id) {
                throw new \InvalidArgumentException('لا يمكن ربط الاقتراح بنفسه');
            }
            // Repeats of this suggestion move to the new original with it.
            Suggestion::where('duplicate_of_id', $suggestion->id)->update(['duplicate_of_id' => $original->id]);
        }
        $suggestion->update(['duplicate_of_id' => $original?->id]);
        $suggestion->history()->create(['user_id' => $by->id, 'action' => 'duplicate',
            'details' => $original ? 'مكرر من ' . $original->reference_code : 'أُلغي ربط التكرار']);
    }

    private function notifySender(Suggestion $suggestion, string $event): void
    {
        $suggestion->loadMissing('user');
        if ($suggestion->user) {
            $suggestion->user->notify(new SuggestionUpdateNotification($suggestion, $event));
        } elseif ($email = $suggestion->senderEmail()) {
            Notification::route('mail', $email)->notify(new SuggestionUpdateNotification($suggestion, $event));
        }
    }
}
