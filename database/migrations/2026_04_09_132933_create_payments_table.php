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
    Schema::create('payments', function (Blueprint $table) {
            $table->id();

            // relasi ke invoice
            $table->foreignId('invoice_id')
                ->constrained()
                ->cascadeOnDelete();

            // relasi ke customer (PENTING 🔥)
            $table->foreignId('customer_id')
                ->constrained()
                ->cascadeOnDelete();

            // nominal pembayaran
            $table->integer('amount');

            // tanggal pembayaran
            $table->date('payment_date');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
