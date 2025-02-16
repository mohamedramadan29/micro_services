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
        Schema::create('properities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->string('title');
            $table->string('slug');
            $table->longText('description');
            $table->enum('type',['بيع','ايجار'])->default('بيع');
            $table->string('category');
            $table->double('price',8,2)->nullable();
            $table->double('area',8,2)->nullable();
            $table->integer('rooms')->nullable();
            $table->integer('bathrooms')->nullable();
            $table->text('features')->nullable();
            $table->string('location');
            $table->string('city');
            $table->string('country');
            $table->enum('status',['متاح','تم البيع','تم الايجار'])->default('متاح');
            $table->tinyInteger('active')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('properities');
    }
};
