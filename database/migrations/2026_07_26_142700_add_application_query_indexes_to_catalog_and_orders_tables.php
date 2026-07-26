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
        Schema::table('items', function (Blueprint $table) {
            $table->index(
                ['origin_id', 'is_listed'],
                'items_origin_id_is_listed_index',
            );
            $table->index(
                ['is_listed', 'view_count', 'id'],
                'items_is_listed_view_count_id_index',
            );
            $table->index(
                ['is_listed', 'sold_count', 'id'],
                'items_is_listed_sold_count_id_index',
            );
            $table->index(
                ['created_at', 'id'],
                'items_created_at_id_index',
            );
            $table->index(
                ['name', 'id'],
                'items_name_id_index',
            );
        });

        Schema::table('item_variations', function (Blueprint $table) {
            $table->index('barcode', 'item_variations_barcode_index');
            $table->index(
                ['item_id', 'stock'],
                'item_variations_item_id_stock_index',
            );
            $table->index('image_id', 'item_variations_image_id_index');
        });

        Schema::table('item_categories', function (Blueprint $table) {
            $table->index(
                ['category_id', 'item_id'],
                'item_categories_category_id_item_id_index',
            );
        });

        Schema::table('item_images', function (Blueprint $table) {
            $table->index(
                ['image_id', 'item_id'],
                'item_images_image_id_item_id_index',
            );
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->index(
                ['created_at', 'id'],
                'orders_created_at_id_index',
            );
            $table->index(
                ['delivery_mode', 'created_at', 'id'],
                'orders_delivery_mode_created_at_id_index',
            );
            $table->index(
                'payment_method_id',
                'orders_payment_method_id_index',
            );
            $table->index(
                'receipt_image_id',
                'orders_receipt_image_id_index',
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex('orders_created_at_id_index');
            $table->dropIndex(
                'orders_delivery_mode_created_at_id_index',
            );
            $table->dropIndex('orders_payment_method_id_index');
            $table->dropIndex('orders_receipt_image_id_index');
        });

        Schema::table('item_images', function (Blueprint $table) {
            $table->dropIndex('item_images_image_id_item_id_index');
        });

        Schema::table('item_categories', function (Blueprint $table) {
            $table->dropIndex('item_categories_category_id_item_id_index');
        });

        Schema::table('item_variations', function (Blueprint $table) {
            $table->dropIndex('item_variations_barcode_index');
            $table->dropIndex('item_variations_item_id_stock_index');
            $table->dropIndex('item_variations_image_id_index');
        });

        Schema::table('items', function (Blueprint $table) {
            $table->dropIndex('items_origin_id_is_listed_index');
            $table->dropIndex('items_is_listed_view_count_id_index');
            $table->dropIndex('items_is_listed_sold_count_id_index');
            $table->dropIndex('items_created_at_id_index');
            $table->dropIndex('items_name_id_index');
        });
    }
};
