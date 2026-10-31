<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** A file of a delivery version (private disk, downloaded through a controller that checks access). */
class BusinessDeliveryFile extends Model
{
    public const DISK = 'private_uploads';

    protected $guarded = [];

    public function delivery()
    {
        return $this->belongsTo(BusinessDelivery::class, 'business_delivery_id');
    }
}
