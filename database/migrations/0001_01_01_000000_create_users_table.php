<?php

use App\Enums\AccessLevel;
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

        Schema::create('users', function (Blueprint $table) use ($supportsPartialIndexes) {
            $table->id();
            $table->string('username');
            $table->string('password');
            $table->string('lang')->default('zh');
            $table->string('timezone')->default('Asia/Kuala_Lumpur');
            $table->integer('access_level')->default(AccessLevel::VIEWER);
            /*
             * 0: can view items and orders (viewer)
             * 1: can update items and orders, include update items listing setting (editor)
             * 2: can create, delete items and update website settings (supervisor)
             * 3: admin
             */
            $table->boolean('is_enabled')->default(false);
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();

            if (! $supportsPartialIndexes) {
                $table->unique(['username', 'deleted_at']);
            }
        });

        if ($supportsPartialIndexes) {
            DB::statement(
                'CREATE UNIQUE INDEX users_username_deleted_at_unique
                ON users (username)
                WHERE deleted_at IS NULL'
            );
        }

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('sessions');
    }
};
