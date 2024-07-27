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
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug');
            $table->integer('cat_id');
            $table->integer('sub_cat_id')->nullable();
            $table->integer('user_id');
            $table->string('image');
            $table->text('description');
            $table->string('tags');
            $table->integer('rate')->default('0');
            $table->string('price');
            $table->tinyInteger('status')->default('0');
            $table->integer('users_num_buy')->default('0');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};
