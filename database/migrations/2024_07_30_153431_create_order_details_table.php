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
        Schema::create('order_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->references('id')->on('orders')->cascadeOnDelete();
            $table->string('order_number');
            $table->integer('user_seller')->comment('صاحب الخدمة ');
            $table->integer('user_buyer')->comment(' صاحب الطلب  ');
            $table->integer('service_id');
            $table->string('service_name');
            $table->string('service_price');
            $table->string('status')->default('لم يبدا');
            $table->string('rate')->nullable();
            $table->string('service_end_time')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_details');
    }
};
