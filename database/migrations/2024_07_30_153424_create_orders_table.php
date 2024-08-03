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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number');
            $table->integer('user_seller')->comment('صاحب الخدمة ');
            $table->integer('user_buyer')->comment(' صاحب الطلب  ');
            $table->string('order_price');
            $table->string('website_commission');
            $table->string('seller_commission');
            $table->string('status')->default('لم يبدا');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
