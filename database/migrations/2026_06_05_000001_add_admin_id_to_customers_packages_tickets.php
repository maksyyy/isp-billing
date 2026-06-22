<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // ── customers ──────────────────────────────────────────────
        Schema::table('customers', function (Blueprint $table) {
            $table->unsignedBigInteger('admin_id')->nullable()->after('id');
            $table->index('admin_id');
        });

        // ── packages ───────────────────────────────────────────────
        Schema::table('packages', function (Blueprint $table) {
            $table->unsignedBigInteger('admin_id')->nullable()->after('id');
            $table->index('admin_id');
        });

        // ── tickets ────────────────────────────────────────────────
        Schema::table('tickets', function (Blueprint $table) {
            $table->unsignedBigInteger('admin_id')->nullable()->after('id');
            $table->index('admin_id');
        });

        // ── invoices ───────────────────────────────────────────────
        Schema::table('invoices', function (Blueprint $table) {
            $table->unsignedBigInteger('admin_id')->nullable()->after('id');
            $table->index('admin_id');
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropIndex(['admin_id']);
            $table->dropColumn('admin_id');
        });
        Schema::table('packages', function (Blueprint $table) {
            $table->dropIndex(['admin_id']);
            $table->dropColumn('admin_id');
        });
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropIndex(['admin_id']);
            $table->dropColumn('admin_id');
        });
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropIndex(['admin_id']);
            $table->dropColumn('admin_id');
        });
    }
};
