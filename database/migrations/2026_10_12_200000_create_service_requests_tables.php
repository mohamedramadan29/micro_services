<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_requests', function (Blueprint $table) {
            $table->id();
            $table->string('number')->nullable()->unique(); // REQ-1001, set right after insert
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('service_form_id')->nullable()->constrained('service_forms')->nullOnDelete();
            $table->unsignedBigInteger('category_id')->nullable()->index();
            $table->unsignedBigInteger('sub_category_id')->nullable()->index();
            // Names copied at submission so the request still reads right if a service is renamed or removed.
            $table->string('category_name')->nullable();
            $table->string('service_name');
            $table->string('path');   // execute | explore
            $table->string('status'); // open | inquiry | … (later phases add more)
            $table->text('problem');
            // Contact details: never sent to freelancers.
            $table->string('contact_name');
            $table->string('contact_phone');
            $table->string('contact_email');
            // Answers mapped to request filters by the form's filter_key.
            $table->decimal('budget_min', 12, 2)->nullable();
            $table->decimal('budget_max', 12, 2)->nullable();
            $table->date('deadline')->nullable();
            $table->string('project_type')->nullable();
            $table->string('work_mode')->nullable();
            $table->string('city')->nullable();
            $table->string('urgency')->nullable();
            $table->string('experience')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'published_at']);
        });

        // Each answer keeps a copy of the question as it was asked.
        Schema::create('service_request_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_request_id')->constrained('service_requests')->cascadeOnDelete();
            $table->unsignedBigInteger('question_id')->nullable()->index();
            $table->string('section_title')->nullable();
            $table->string('label');
            $table->string('type');
            $table->json('value')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        // Uploaded files: per-question answers and the shared attachments (question_id null).
        Schema::create('service_request_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_request_id')->constrained('service_requests')->cascadeOnDelete();
            $table->unsignedBigInteger('question_id')->nullable();
            $table->string('original_name');
            $table->string('path');
            $table->string('mime')->nullable();
            $table->unsignedBigInteger('size')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_request_files');
        Schema::dropIfExists('service_request_answers');
        Schema::dropIfExists('service_requests');
    }
};
