<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->string('hero_title_1')->nullable()->after('sheets_token_json');
            $table->string('hero_title_2')->nullable()->after('hero_title_1');
            $table->string('hero_title_3')->nullable()->after('hero_title_2');
            $table->text('hero_subtitle')->nullable()->after('hero_title_3');
            $table->string('hero_image')->nullable()->after('hero_subtitle');
            $table->string('hero_overlay_color')->default('#00000057')->after('hero_image');
        });
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn([
                'hero_title_1',
                'hero_title_2',
                'hero_title_3',
                'hero_subtitle',
                'hero_image',
                'hero_overlay_color',
            ]);
        });
    }
};
