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
        Schema::create('images', function (Blueprint $table) {
            $table->id();

            $table->string('name')->nullable();
            $table->string('mime_type');
            $table->unsignedBigInteger('size');

            $table->string('url', 1000)->nullable();
            $table->text('data_uri')->nullable();
            $table->foreignId('thumbnail_id')
                ->nullable()
                ->unique()
                ->constrained('images')
                ->nullOnDelete();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('images');
    }
};
