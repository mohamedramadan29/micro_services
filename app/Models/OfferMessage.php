<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OfferMessage extends Model
{
    protected $guarded = [];

    protected $casts = [
        'read_at' => 'datetime',
    ];

    public function offer()
    {
        return $this->belongsTo(RequestOffer::class, 'request_offer_id');
    }

    public function request()
    {
        return $this->belongsTo(ServiceRequest::class, 'service_request_id');
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }
}
