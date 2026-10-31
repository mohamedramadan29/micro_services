<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/** Client file «نظام الاقتراحات والتطوير المستمر» (2026-10-18): suggestions from clients, freelancers and visitors. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('suggestion_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name', 80);
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        $now = now();
        DB::table('suggestion_categories')->insert(collect(['ميزة جديدة', 'تحسين ميزة', 'تحسين تجربة الاستخدام', 'اقتراح عام'])
            ->map(fn ($name, $i) => ['name' => $name, 'sort_order' => $i + 1, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now])->all());

        Schema::create('suggestions', function (Blueprint $table) {
            $table->id();
            $table->string('reference_code', 20)->nullable()->unique();
            // Idempotency: one row per form submission (a double click or a resend returns the same suggestion).
            $table->string('submission_key', 64)->unique();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('user_role', 20); // client | freelancer | employee | admin | guest (snapshot)
            $table->string('guest_name', 100)->nullable();
            $table->string('guest_email', 150)->nullable();
            $table->boolean('wants_updates')->default(false);
            $table->foreignId('category_id')->nullable()->constrained('suggestion_categories')->nullOnDelete();
            $table->string('category_name', 80)->nullable(); // snapshot, kept if the type is renamed or removed
            $table->string('title', 120);
            $table->text('description');
            $table->string('status', 20)->default('new');
            $table->text('status_reason')->nullable(); // last reason shown to the sender
            $table->foreignId('assigned_admin_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('internal_priority', 10)->default('normal');
            $table->foreignId('duplicate_of_id')->nullable()->constrained('suggestions')->nullOnDelete();
            $table->string('ip', 45)->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
            $table->index(['user_id', 'created_at']);
        });

        Schema::create('suggestion_status_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('suggestion_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete(); // staff member; null = the sender / system
            $table->string('action', 20); // created | status | assigned | priority | duplicate
            $table->string('from_status', 20)->nullable();
            $table->string('to_status', 20)->nullable();
            $table->text('details')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::create('suggestion_admin_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('suggestion_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->text('body');
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('suggestion_admin_notes');
        Schema::dropIfExists('suggestion_status_history');
        Schema::dropIfExists('suggestions');
        Schema::dropIfExists('suggestion_categories');
    }
};
