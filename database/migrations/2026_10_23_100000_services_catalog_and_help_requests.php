<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Client brief «متطلبات تطوير قسم الخدمات»: the services page shows groups
 * (categories) and their services (sub-categories) with admin-managed texts,
 * icons, order and links; «ساعدوني أختار» help requests go to their own box.
 */
return new class extends Migration
{
    public function up(): void
    {
        foreach (['categories', 'sub_categories'] as $table) {
            Schema::table($table, function (Blueprint $t) use ($table) {
                $t->string('description', 500)->nullable();
                $t->string('icon', 60)->nullable();
                $t->unsignedInteger('sort_order')->default(0);
                if ($table === 'sub_categories') {
                    $t->string('link', 500)->nullable();        // custom page; empty = the request form of this service
                    $t->string('button_text', 100)->nullable();
                }
            });
            Schema::table($table, fn (Blueprint $t) => $t->string('image')->nullable()->change());
        }

        Schema::create('help_requests', function (Blueprint $table) {
            $table->id();
            $table->string('number', 20)->nullable()->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sub_category_id')->nullable()->constrained('sub_categories')->nullOnDelete();
            $table->string('service_name')->nullable(); // snapshot; null = «استفسار عام»
            $table->string('whatsapp', 30);
            $table->text('message');
            $table->string('status', 20)->default('new');
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['status', 'created_at']);
        });

        Schema::create('help_request_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('help_request_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->text('body');
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('help_request_notes');
        Schema::dropIfExists('help_requests');
        foreach (['categories', 'sub_categories'] as $table) {
            Schema::table($table, function (Blueprint $t) use ($table) {
                $t->dropColumn(array_merge(['description', 'icon', 'sort_order'], $table === 'sub_categories' ? ['link', 'button_text'] : []));
            });
        }
    }
};
