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
        Schema::table('backbone_devices', function (Blueprint $table) {
            $table->timestamp('first_failed_at')->nullable()->after('last_ping_at');
            $table->boolean('telegram_alert_sent')->default(false)->after('first_failed_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('backbone_devices', function (Blueprint $table) {
            $table->dropColumn(['first_failed_at', 'telegram_alert_sent']);
        });
    }
};
