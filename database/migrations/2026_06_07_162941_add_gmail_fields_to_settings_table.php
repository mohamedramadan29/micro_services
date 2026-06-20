<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->text('gmail_credentials_json')->nullable()->after('website_commission');
            $table->text('gmail_token_json')->nullable()->after('gmail_credentials_json');
        });
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn(['gmail_credentials_json', 'gmail_token_json']);
        });
    }
};
