<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** A file sent with a company request (private disk, downloaded through a controller that checks access). */
class BusinessRequestFile extends Model
{
    public const DISK = 'private_uploads';

    protected $guarded = [];

    public function request()
    {
        return $this->belongsTo(BusinessRequest::class, 'business_request_id');
    }
}
