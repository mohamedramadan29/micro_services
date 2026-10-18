<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // One request form per service (sub-category), built by the admin.
        Schema::create('service_forms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sub_category_id')->unique()->constrained('sub_categories')->cascadeOnDelete();
            $table->string('status')->default('draft'); // draft | published | paused
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });

        Schema::create('service_form_sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_form_id')->constrained('service_forms')->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('service_form_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_form_id')->constrained('service_forms')->cascadeOnDelete();
            $table->foreignId('service_form_section_id')->constrained('service_form_sections')->cascadeOnDelete();
            $table->string('type');
            $table->string('label');
            $table->text('help_text')->nullable();
            $table->string('placeholder')->nullable();
            $table->boolean('is_required')->default(false);
            $table->json('options')->nullable();   // choices for choice types
            $table->json('settings')->nullable();  // per-type limits (length, range, file types…)
            $table->json('condition')->nullable(); // {question_id, values[], negate}
            $table->string('filter_key')->nullable(); // maps the answer to a request filter
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_form_questions');
        Schema::dropIfExists('service_form_sections');
        Schema::dropIfExists('service_forms');
    }
};
