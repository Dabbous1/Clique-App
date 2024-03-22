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
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('product_id')->index();
            $table->longText('product_tags');
            $table->string('product_title');
            $table->string('product_handle');
            $table->string('product_vendor');
            $table->string('product_image');
            $table->longText('description');
            $table->string('status');
            $table->unsignedBigInteger('option_set_id')->nullable();
            $table->string('product_created_at')->nullable();
            // $table->longText('variants_json');
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
