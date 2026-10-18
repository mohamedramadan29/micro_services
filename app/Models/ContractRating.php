<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** A rating after a completed project: client → freelancer (public) or freelancer → client (freelancers + admin only). */
class ContractRating extends Model
{
    public const CLIENT_TO_FREELANCER = 'client_to_freelancer';
    public const FREELANCER_TO_CLIENT = 'freelancer_to_client';

    protected $guarded = [];

    public function rater()
    {
        return $this->belongsTo(User::class, 'rater_id');
    }
}
