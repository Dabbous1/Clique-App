<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('order_id')->nullable();
            $table->string('email')->nullable();
            $table->string('contact_email')->nullable();
            $table->string('phone_no')->nullable();
            $table->string('order_created_at')->nullable();
            $table->string('customer_name')->nullable();
            $table->decimal('total', 8, 2);
            $table->string('payment_status')->nullable();
            $table->string('fulfillment_status')->nullable();
            $table->string('order_name')->nullable();
            $table->string('customer_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('orders');
    }
};
