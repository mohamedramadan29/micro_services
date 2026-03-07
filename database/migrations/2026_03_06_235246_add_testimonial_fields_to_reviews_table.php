<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            $table->string('client_position')->nullable()->after('name');
            $table->string('client_company')->nullable()->after('client_position');
            $table->string('client_image')->nullable()->after('client_company');
            $table->integer('rating')->default(5)->after('client_image');
            $table->boolean('is_featured')->default(false)->after('rating');
            $table->boolean('is_active')->default(true)->after('is_featured');
            $table->integer('sort_order')->default(0)->after('is_active');

            // Rename existing columns for clarity
            $table->renameColumn('name', 'client_name');
            $table->renameColumn('more_notes', 'content');
            $table->renameColumn('status', 'is_approved');

            // Drop unnecessary column
            $table->dropColumn('reviews_count');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            $table->dropColumn([
                'client_position',
                'client_company',
                'client_image',
                'rating',
                'is_featured',
                'is_active',
                'sort_order'
            ]);

            // Restore original column names
            $table->renameColumn('client_name', 'name');
            $table->renameColumn('content', 'more_notes');
            $table->renameColumn('is_approved', 'status');

            // Restore dropped column
            $table->integer('reviews_count')->default(0);
        });
    }
};
