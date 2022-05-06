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
        Schema::create('variation_discounts', function (Blueprint $table) {
            $table->string('barcode')->primary();
            $table->date('start');
            $table->date('end')->nullable();
            $table->double('rate')->default(1.0);
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('barcode')->references('barcode')->on('variations');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('variation_discounts');
    }
};
