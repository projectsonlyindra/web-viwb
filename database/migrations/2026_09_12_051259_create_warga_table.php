<?php

use App\Enums\JenisKendaraan;
use App\Enums\StatusWarga;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('warga', function (Blueprint $table) {
            $table->id();
            $table->string('nik', 16)->unique();
            $table->string('nama');
            $table->string('no_wa');
            $table->string('unit_id')->unique();
            $table->string('jenis_kendaraan')->default(JenisKendaraan::TIDAK_ADA->value);
            $table->string('status_warga')->default(StatusWarga::AKTIF->value);
            $table->boolean('ikut_hippam')->default(false);
            $table->boolean('ikut_kebersihan')->default(false);
            $table->timestamps();

            $table->index('unit_id');
            $table->index('no_wa');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('warga');
    }
};
