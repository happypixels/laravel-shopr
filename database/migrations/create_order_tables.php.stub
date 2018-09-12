<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrderTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->nullable()->unsigned();
            $table->string('payment_status')->default('pending');
            $table->string('delivery_status')->default('pending');
            $table->string('token')->unique();
            $table->string('payment_gateway')->nullable();
            $table->string('transaction_id')->nullable();
            $table->string('transaction_reference')->nullable();
            $table->decimal('total', 9, 2)->default(0);
            $table->decimal('sub_total', 9, 2)->default(0);
            $table->decimal('tax', 9, 2)->default(0);
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('address')->nullable();
            $table->string('zipcode')->nullable();
            $table->string('city')->nullable();
            $table->string('country')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('order_id')->nullable()->unsigned();
            $table->integer('parent_id')->nullable()->unsigned();
            $table->string('shoppable_type');
            $table->string('shoppable_id');
            $table->integer('quantity')->default(1);
            $table->string('title');
            $table->decimal('price', 9, 2)->default(0);
            $table->json('options')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
            $table->foreign('parent_id')->references('id')->on('order_items')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('order_items', function ($table) {
            $table->dropForeign('order_items_order_id_foreign');
        });

        Schema::table('orders', function ($table) {
            $table->dropForeign('orders_user_id_foreign');
        });

        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
    }
}
