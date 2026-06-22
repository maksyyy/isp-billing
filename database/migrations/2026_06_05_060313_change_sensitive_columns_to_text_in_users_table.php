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
        Schema::table('users', function (Blueprint $table) {
            $table->text('prtg_password')->nullable()->change();
            $table->text('mikrotik_password')->nullable()->change();
            $table->text('telegram_bot_token')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('prtg_password', 255)->nullable()->change();
            $table->string('mikrotik_password', 255)->nullable()->change();
            $table->string('telegram_bot_token', 255)->nullable()->change();
        });
    }
};
