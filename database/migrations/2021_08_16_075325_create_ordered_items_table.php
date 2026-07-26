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
        Schema::create('ordered_items', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->string('name_en');
            $table->string('barcode');
            $table->decimal('price');
            $table->decimal('sale_price')->nullable();
            $table->integer('quantity');

            $table->foreignId('order_id')
                ->references('id')
                ->on('orders')
                ->cascadeOnDelete();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ordered_items');
    }
};
