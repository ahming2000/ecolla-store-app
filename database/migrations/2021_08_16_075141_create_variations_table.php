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
        Schema::create('variations', function (Blueprint $table) {
            $table->string('barcode')->primary();
            $table->string('name');
            $table->string('name_en')->nullable();
            $table->double('price')->default(0);
            $table->double('weight')->default(0);
            $table->string('image')->nullable();
            $table->integer('stock')->default(0);
            $table->date('discount_start')->nullable();
            $table->date('discount_end')->nullable();
            $table->double('discount_rate')->default(1.0);
            $table->foreignId('item_id')->constrained('items');
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
        Schema::dropIfExists('variations');
    }
};
