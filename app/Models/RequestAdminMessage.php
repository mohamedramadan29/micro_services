<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RequestAdminMessage extends Model
{
    protected $guarded = [];

    /** Client classifications are cached; any change here makes them stale. */
    protected static function booted(): void
    {
        static::saved(fn () => \App\Services\ServiceRequests\ClientClassifier::forget());
        static::deleted(fn () => \App\Services\ServiceRequests\ClientClassifier::forget());
    }

    protected $casts = [
        'from_admin' => 'boolean',
        'read_at' => 'datetime',
    ];

    public function request()
    {
        return $this->belongsTo(ServiceRequest::class, 'service_request_id');
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }
}
