<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();

            // relasi ke customer
            $table->foreignId('customer_id')->constrained()->onDelete('cascade');

            // jumlah tagihan
            $table->integer('amount');

            // tanggal jatuh tempo
            $table->date('due_date');

            // status pembayaran
            $table->string('status')->default('unpaid');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};