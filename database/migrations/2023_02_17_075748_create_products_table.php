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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('name')->nullable();
            $table->string('status')->nullable();
            $table->unsignedBigInteger('shopify_id')->nullable();
            $table->string('code')->nullable();
            $table->string('brand')->nullable();
            $table->string('category')->nullable();
            $table->string('sub_category')->nullable();
            $table->integer('qty')->nullable();
            $table->decimal('unit_cost_eur')->nullable();
            $table->decimal('unit_cost_usd')->nullable();
            $table->decimal('unit_cost_egp')->nullable();
            $table->decimal('cost_of_gram_usd')->nullable();
            $table->decimal('unit_weight_gram')->nullable();
            $table->decimal('unit_cost_with_weight_cost_usd')->nullable();
            $table->decimal('unit_cost_with_weight_cost_egp')->nullable();
            $table->decimal('final_price_egp')->nullable();
            $table->decimal('original_price')->nullable();
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
        Schema::dropIfExists('products');
    }
};
