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
        Schema::create('order_line_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger("local_order_id");
            $table->string("order_id");
            $table->string("line_item_id");
            $table->string("product_id")->nullable()->index();
            $table->string("name")->nullable();
            $table->decimal("price", 8, 2)->nullable();
            $table->string("quantity")->nullable();
            $table->string("weight")->nullable();
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
        Schema::dropIfExists('order_line_items');
    }
};
