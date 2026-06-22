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
        Schema::create('backbone_devices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('admin_id');
            $table->string('name');
            $table->string('ip');
            $table->string('status')->default('up'); // 'up' or 'down'
            $table->timestamp('last_ping_at')->nullable();
            $table->timestamps();

            $table->foreign('admin_id')->references('id')->on('users')->onDelete('cascade');
            $table->index('admin_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('backbone_devices');
    }
};
