<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
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

            $table->index(
                ['item_id', 'stock'],
                'item_variations_item_id_stock_index',
            );
            $table->index(
                'image_id',
                'item_variations_image_id_index',
            );
        });

        $supportsPartialIndexes = in_array(
            DB::connection()->getDriverName(),
            ['pgsql', 'sqlite'],
            true,
        );

        if ($supportsPartialIndexes) {
            DB::statement(
                'CREATE UNIQUE INDEX item_variations_barcode_deleted_at_unique
                ON item_variations (barcode)
                WHERE deleted_at IS NULL'
            );
        } else {
            Schema::table('item_variations', function (Blueprint $table) {
                $table->unique(
                    ['barcode', 'deleted_at'],
                    'item_variations_barcode_deleted_at_unique',
                );
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('item_variations');
    }
};
