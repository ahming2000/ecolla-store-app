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
        Schema::create('items', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->string('name_en')->nullable();
            $table->string('slug')->unique();
            $table->text('desc')->nullable();

            $table->boolean('is_listed')->default(false);
            $table->unsignedBigInteger('view_count')->default(0);
            $table->unsignedBigInteger('sold_count')->default(0);

            $table->foreignId('origin_id')
                ->nullable()
                ->references('id')
                ->on('origins')
                ->restrictOnDelete();

            $table->timestamps();
            $table->softDeletes();

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
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};
