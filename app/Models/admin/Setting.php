<?php

namespace App\Models\admin;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    protected $guarded = [];

    /** Client classifications are cached; any change here makes them stale. */
    protected static function booted(): void
    {
        static::saved(fn () => \App\Services\ServiceRequests\ClientClassifier::forget());
    }

    protected $casts = [
        'request_notification_emails' => 'array',
        'site_test_settings' => 'array',
        'contract_policy' => 'array',
        'notification_templates' => 'array',
    ];
}
