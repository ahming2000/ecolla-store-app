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
        Schema::create('item_variations', function (Blueprint $table) {
            $table->id();

            $table->string('barcode');
            $table->string('name');
            $table->string('name_en')->nullable();

            $table->decimal('price')->default(0);
            $table->decimal('sale_price')->nullable();

            $table->decimal('weight')->default(0);
            $table->unsignedBigInteger('stock')->default(0);

            $table->foreignId('image_id')
                ->nullable()
                ->references('id')
                ->on('images');
            $table->foreignId('item_id')
                ->references('id')
                ->on('items')
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
        Schema::dropIfExists('variations');
    }
};
