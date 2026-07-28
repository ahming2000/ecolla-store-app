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

        Schema::table('item_variations', function (Blueprint $table) {
            $table->dropIndex('item_variations_barcode_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('item_variations', function (Blueprint $table) {
            $table->index('barcode', 'item_variations_barcode_index');
            $table->dropUnique(
                'item_variations_barcode_deleted_at_unique',
            );
        });
    }
};
