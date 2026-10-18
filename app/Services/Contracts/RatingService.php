<?php

namespace App\Services\Contracts;

use App\Models\Contract;
use App\Models\ContractRating as R;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Ratings after a completed project (client decision 2026-10-16), optional, once per side.
 * The client's rating feeds the freelancer's public `rate` (directory, ordering); the
 * freelancer's rating of the client is shown to freelancers and the admin only.
 */
class RatingService
{
    public function rate(Contract $contract, User $user, int $stars, ?string $comment): R
    {
        return DB::transaction(function () use ($contract, $user, $stars, $comment) {
            $contract = Contract::whereKey($contract->id)->lockForUpdate()->first();
            if ($contract->status !== Contract::STATUS_COMPLETED) {
                throw new RuntimeException('التقييم بعد اكتمال المشروع');
            }
            $isClient = (int) $user->id === (int) $contract->client_id;
            if (! $isClient && (int) $user->id !== (int) $contract->freelancer_id) {
                throw new RuntimeException('غير مسموح');
            }
            if (R::where('contract_id', $contract->id)->where('rater_id', $user->id)->exists()) {
                throw new RuntimeException('قيّمت هذا المشروع بالفعل');
            }
            $ratee = $isClient ? $contract->freelancer_id : $contract->client_id;
            $direction = $isClient ? R::CLIENT_TO_FREELANCER : R::FREELANCER_TO_CLIENT;
            $rating = R::create(['contract_id' => $contract->id, 'rater_id' => $user->id, 'ratee_id' => $ratee, 'direction' => $direction, 'stars' => $stars, 'comment' => $comment]);

            $all = R::where('ratee_id', $ratee)->where('direction', $direction);
            $average = round((float) $all->avg('stars'), 2);
            $count = $all->count();
            User::whereKey($ratee)->update($isClient
                ? ['rate' => $average, 'rating_count' => $count]
                : ['client_rating' => $average, 'client_rating_count' => $count]);

            return $rating;
        });
    }
}
