<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE users MODIFY role VARCHAR(50) NOT NULL DEFAULT 'admin'");
        }

        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'parent_admin_id')) {
                $table->foreignId('parent_admin_id')
                    ->nullable()
                    ->after('role')
                    ->constrained('users')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'parent_admin_id')) {
                $table->dropConstrainedForeignId('parent_admin_id');
            }
        });
    }
};
