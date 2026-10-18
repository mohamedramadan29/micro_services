<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceRequestFile extends Model
{
    /** Request files live on the private local disk, never under public/. */
    public const DISK = 'private_uploads'; // public/uploads/private, closed to direct access

    protected $guarded = [];

    public function request()
    {
        return $this->belongsTo(ServiceRequest::class, 'service_request_id');
    }

    public function humanSize(): string
    {
        $kb = $this->size / 1024;

        return $kb >= 1024 ? round($kb / 1024, 1) . ' MB' : max(1, round($kb)) . ' KB';
    }
}
