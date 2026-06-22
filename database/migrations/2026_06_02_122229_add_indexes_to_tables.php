<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('presensis', function (Blueprint $table) {
            $table->index('tanggal');
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->index('status');
        });

        Schema::table('tickets', function (Blueprint $table) {
            $table->index('status');
            $table->index('tanggal');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('presensis', function (Blueprint $table) {
            $table->dropIndex(['tanggal']);
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->dropIndex(['status']);
        });

        Schema::table('tickets', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['tanggal']);
        });
    }
};
