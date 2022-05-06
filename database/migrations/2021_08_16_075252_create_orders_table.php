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
            $table->string('id')->primary();
            $table->integer('mode')->default(0);
            /*
             * 0: pickup
             * 1: delivery
             */
            $table->string('tracking_id')->nullable();
            $table->double('shipping_fee')->default(0.0);
            $table->foreignId('payment_method')->constrained('payment_methods');
            $table->integer('status')->default(0);
            /*
             * 0: processing
             * 1: completed
             * 2: canceled
             */
            $table->string('receipt_image');
            $table->string('note')->nullable();
            $table->timestamps();
            $table->softDeletes();
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
