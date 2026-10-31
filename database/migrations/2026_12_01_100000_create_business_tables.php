<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * «نفّذها للأعمال | للشركات» (client file «قسم الشركات», phase 1 / MVP, 2026-10-31): requests from companies
 * (free text or the guided form), the team's quote, projects with tasks, deliveries with versions and the
 * client's approval, and one audit log. The Nafizha team executes (no freelancers); one user per company.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('business_companies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete(); // one user per company (for now)
            $table->string('name', 150);
            $table->string('size', 10)->nullable(); // small | medium | large
            $table->string('activity', 150)->nullable();
            $table->string('country', 80)->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('website', 255)->nullable();
            $table->timestamps();
        });

        Schema::create('business_projects', function (Blueprint $table) {
            $table->id();
            $table->string('number', 20)->nullable()->unique();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete(); // the client account
            $table->foreignId('manager_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('title', 150);
            $table->text('description')->nullable();
            $table->decimal('amount', 12, 2)->default(0);
            $table->string('status', 20)->default('active'); // active | completed | cancelled
            $table->date('due_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('business_requests', function (Blueprint $table) {
            $table->id();
            $table->string('number', 20)->nullable()->unique();
            $table->string('submission_key', 64)->unique(); // idempotency (double click / resend)
            $table->string('source', 10); // free | guided (kept forever: the two paths end in the same inbox)
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('contact_name', 100)->nullable();
            $table->string('phone', 30);
            $table->string('email', 150)->nullable();
            $table->string('company_name', 150)->nullable();
            $table->string('company_size', 10)->nullable();
            $table->longText('details')->nullable(); // the free text
            $table->json('answers')->nullable();     // guided form snapshot [{section,label,type,value}]
            $table->string('status', 20)->default('new');
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('consent_at')->nullable();
            // The team's quote (one current quote; every change is in the log).
            $table->decimal('quote_amount', 12, 2)->nullable();
            $table->text('quote_scope')->nullable();
            $table->date('quote_due_at')->nullable();
            $table->timestamp('quote_sent_at')->nullable();
            $table->timestamp('quote_decided_at')->nullable();
            $table->text('decision_note')->nullable();
            // Payment: online (EasyKash) or recorded by the admin (cash, transfer…).
            $table->string('payment_status', 10)->default('unpaid'); // unpaid | paid
            $table->string('payment_method', 20)->nullable();
            $table->string('payment_reference', 120)->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->foreignId('business_project_id')->nullable()->constrained()->nullOnDelete();
            $table->string('ip', 45)->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
        });

        Schema::create('business_request_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_request_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('question_id')->nullable();
            $table->string('original_name');
            $table->string('path');
            $table->string('mime', 120)->nullable();
            $table->unsignedBigInteger('size')->default(0);
            $table->timestamps();
        });

        Schema::create('business_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_project_id')->constrained()->cascadeOnDelete();
            $table->string('title', 150);
            $table->text('description')->nullable();
            $table->foreignId('assignee_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status', 20)->default('todo'); // todo | in_progress | review | done
            $table->string('priority', 10)->default('normal');
            $table->date('due_at')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('business_deliveries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('business_task_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('root_id')->nullable()->constrained('business_deliveries')->nullOnDelete(); // v1 of this deliverable
            $table->unsignedInteger('version')->default(1);
            $table->string('title', 150);
            $table->text('note')->nullable();
            $table->json('links')->nullable();
            $table->string('status', 20)->default('pending'); // pending | approved | changes_requested
            $table->text('client_comment')->nullable();
            $table->foreignId('submitted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('business_delivery_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_delivery_id')->constrained()->cascadeOnDelete();
            $table->string('original_name');
            $table->string('path');
            $table->string('mime', 120)->nullable();
            $table->unsignedBigInteger('size')->default(0);
            $table->timestamps();
        });

        Schema::create('business_events', function (Blueprint $table) {
            $table->id();
            $table->morphs('subject'); // a request or a project
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete(); // who; null = the visitor / system
            $table->string('action', 30);
            $table->text('details')->nullable();
            $table->boolean('internal')->default(false); // team notes: never shown to the client
            $table->timestamp('created_at')->useCurrent();
        });

        // The guided form lives in «نماذج الخدمات» under a hidden group, so the admin edits it in the existing builder.
        (new \Database\Seeders\BusinessFormSeeder)->run();
    }

    public function down(): void
    {
        foreach (['business_events', 'business_delivery_files', 'business_deliveries', 'business_tasks', 'business_request_files', 'business_requests', 'business_projects', 'business_companies'] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
