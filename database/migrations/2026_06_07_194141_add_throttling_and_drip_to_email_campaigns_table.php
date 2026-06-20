<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('email_campaigns', function (Blueprint $table) {
            $table->unsignedSmallInteger('send_interval_seconds')->default(10)->after('tracking_enabled')->comment('مدة التأخير بين كل إيميل وآخر بالثواني');
            $table->unsignedSmallInteger('throttle_per_hour')->default(100)->after('send_interval_seconds')->comment('الحد الأقصى للإرسال في الساعة');
            $table->unsignedSmallInteger('daily_limit')->default(500)->after('throttle_per_hour')->comment('الحد الأقصى للإرسال في اليوم');
            $table->boolean('drip_enabled')->default(false)->after('daily_limit');
            $table->unsignedTinyInteger('drip_total_parts')->default(1)->after('drip_enabled');
            $table->unsignedTinyInteger('drip_current_part')->default(0)->after('drip_total_parts');
            $table->unsignedSmallInteger('drip_interval_hours')->default(24)->after('drip_current_part');
        });
    }

    public function down(): void
    {
        Schema::table('email_campaigns', function (Blueprint $table) {
            $table->dropColumn([
                'send_interval_seconds',
                'throttle_per_hour',
                'daily_limit',
                'drip_enabled',
                'drip_total_parts',
                'drip_current_part',
                'drip_interval_hours',
            ]);
        });
    }
};
