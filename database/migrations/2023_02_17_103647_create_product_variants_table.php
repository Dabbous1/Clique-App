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
        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("product_id")->nullable();
            $table->unsignedBigInteger("shopify_id")->nullable();
            $table->string('code')->nullable();
            $table->integer("position")->nullable();
            $table->decimal("weight" , 13 , 3)->nullable();
            $table->decimal('unit_cost_eur' , 13 , 3)->nullable();
            $table->decimal('unit_cost_usd' , 13 , 3)->nullable();
            $table->decimal('unit_cost_egp' , 13 , 3)->nullable();
            $table->decimal('cost_of_gram_usd' , 13 , 3)->nullable();
            $table->decimal('unit_weight_gram' , 13 , 3)->nullable();
            $table->decimal('unit_cost_with_weight_cost_usd' , 13 , 3)->nullable();
            $table->decimal('unit_cost_with_weight_cost_egp' , 13 , 3)->nullable();
            $table->decimal('final_price_egp' , 13 , 3)->nullable();
            $table->decimal('original_price' , 13 , 3)->nullable();
            $table->integer("qty")->nullable();
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
        Schema::dropIfExists('product_variants');
    }
};
