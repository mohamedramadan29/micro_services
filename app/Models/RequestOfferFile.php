<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RequestOfferFile extends Model
{
    /** Private local disk, like request files. */
    public const DISK = 'private_uploads'; // public/uploads/private, closed to direct access

    protected $guarded = [];

    public function offer()
    {
        return $this->belongsTo(RequestOffer::class, 'request_offer_id');
    }

    public function humanSize(): string
    {
        $kb = $this->size / 1024;

        return $kb >= 1024 ? round($kb / 1024, 1) . ' MB' : max(1, round($kb)) . ' KB';
    }
}
