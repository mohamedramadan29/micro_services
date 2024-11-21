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
        Schema::create('project_offers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreignId('project_id')->references('id')->on('projects')->cascadeOnDelete();
            $table->integer('day_number');
            $table->double('offer_price',8,2);
            $table->double('user_get',8,2);
            $table->double('website_get',8,2);
            $table->text('proposal');
            $table->string('file')->nullable();
            $table->tinyInteger('status')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_offers');
    }
};
