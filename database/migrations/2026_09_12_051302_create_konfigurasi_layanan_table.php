<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('konfigurasi_layanan', function (Blueprint $table) {
            $table->id();
            $table->string('jenis')->unique();
            $table->integer('nominal');
            $table->integer('nominal_mobil')->nullable();
            $table->integer('nominal_tanpa_mobil')->nullable();
            $table->integer('cutoff_hari');
            $table->integer('denda_harian');
            $table->integer('denda_maksimal');
            $table->integer('alert_tunggakan_bulan')->default(3);
            $table->integer('tarif_per_m3')->nullable();
            $table->integer('minimal_m3')->nullable();
            $table->integer('minimal_nominal')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('konfigurasi_layanan');
    }
};
