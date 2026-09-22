<?php

use App\Enums\StatusTagihan;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tagihan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warga_id')->constrained('warga')->cascadeOnDelete();
            $table->string('jenis');
            $table->string('periode');
            $table->integer('nominal');
            $table->string('status')->default(StatusTagihan::BELUM_BAYAR->value);
            $table->integer('cutoff_hari');
            $table->integer('denda_harian');
            $table->integer('denda_maksimal');
            $table->date('tanggal_jatuh_tempo');
            $table->integer('meter_awal')->nullable();
            $table->integer('meter_akhir')->nullable();
            $table->integer('pemakaian_m3')->nullable();
            $table->timestamps();

            $table->unique(['warga_id', 'jenis', 'periode']);
            $table->index(['status', 'jenis', 'periode']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tagihan');
    }
};
