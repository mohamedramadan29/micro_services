<?php

namespace App\Services\Admin;

use App\Models\Contract;
use App\Models\RequestPayment;
use App\Models\User;
use App\Models\WalletTransaction;
use App\Models\admin\Service;
use App\Models\front\Conversation;
use App\Models\front\Message;
use App\Models\front\WithDraw;
use Illuminate\Support\Facades\DB;

/**
 * Permanent deletion of an account by the admin (user request 2026-10-31: delete test freelancers directly).
 *
 * Refused while the account has money or project history — deleting it would cascade-delete payment
 * records and leave contracts without their freelancer. Those accounts are blocked instead. Everything
 * else that belongs to the account (chats, ready-made services, offers, portfolio, specializations,
 * notifications) goes with it.
 */
class UserDeletion
{
    /** Why the account cannot be deleted (empty = it can). */
    public function blockers(User $user): array
    {
        $reasons = [];
        if ($user->account_type === User::TYPE_ADMIN) {
            $reasons[] = 'حسابات الإدارة لا تُحذف من هنا';
        }
        if (Contract::where('freelancer_id', $user->id)->orWhere('client_id', $user->id)->exists()) {
            $reasons[] = 'له مشروع بمراحل (عقد) في سجل المنصة';
        }
        if (RequestPayment::where(fn ($q) => $q->where('freelancer_id', $user->id)->orWhere('client_id', $user->id))
            ->whereNotIn('status', [RequestPayment::STATUS_PENDING, RequestPayment::STATUS_CANCELLED])->exists()) {
            $reasons[] = 'له مدفوعات على طلبات (محجوزة أو مصروفة أو مستردة)';
        }
        if ((float) $user->balance > 0) {
            $reasons[] = 'رصيده ' . \App\Support\Money::format($user->balance) . ' لم يُسحب';
        }
        if (WithDraw::where('user_id', $user->id)->where('status', 0)->exists()) {
            $reasons[] = 'له طلب سحب معلّق';
        }
        if (WalletTransaction::where('user_id', $user->id)->exists()) {
            $reasons[] = 'له حركات في المحفظة';
        }

        return $reasons;
    }

    public function delete(User $user): void
    {
        if ($reasons = $this->blockers($user)) {
            throw new \RuntimeException(implode('، ', $reasons));
        }

        DB::transaction(function () use ($user) {
            // Chats have plain foreign keys to users (no cascade): remove them first.
            $conversations = Conversation::where('sender_id', $user->id)->orWhere('receiver_id', $user->id)->pluck('id');
            Message::whereIn('conversation_id', $conversations)->orWhere('sender_id', $user->id)->orWhere('receiver_id', $user->id)->delete();
            Conversation::whereIn('id', $conversations)->delete();
            // Tables without a foreign key: his ready-made services and old withdrawal rows.
            Service::where('user_id', $user->id)->delete();
            WithDraw::where('user_id', $user->id)->delete();
            RequestPayment::where('freelancer_id', $user->id)->orWhere('client_id', $user->id)->delete(); // only pending / cancelled checkouts are left here
            $user->notifications()->delete();
            // Offers, offer messages, specializations, portfolio, ratings, help requests… cascade with the user.
            $user->delete();
        });
    }
}
