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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();

            $table->string('reference_num')
                ->index('orders_reference_num_index');
            $table->enum('delivery_mode', ['外送', '预购取货'])
                ->default('外送');
            $table->enum('status', [
                '处理中',
                '准备就绪',
                '已完成',
                '已退款',
                '已取消',
            ])
                ->default('处理中');

            $table->foreignId('payment_method_id')
                ->references('id')
                ->on('payment_methods')
                ->restrictOnDelete();

            $table->string('tracking_no')->nullable();
            $table->decimal('shipping_fee')->default(0.0);

            $table->foreignId('receipt_image_id')
                ->references('id')
                ->on('images')
                ->restrictOnDelete();

            $table->string('note')->nullable();

            $table->string('cus_name')->nullable();
            $table->string('cus_phone');
            $table->string('cus_address')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(
                ['status', 'created_at'],
                'orders_status_created_at_index',
            );
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
        Schema::dropIfExists('orders');
    }
};
