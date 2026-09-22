<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pembayaran_item', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pembayaran_id')->constrained('pembayaran')->cascadeOnDelete();
            $table->foreignId('tagihan_id')->constrained('tagihan')->cascadeOnDelete();
            $table->integer('nominal');
            $table->integer('denda_dibayar')->default(0);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pembayaran_item');
    }
};
