<?php

use App\Enums\StatusPengeluaran;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pengeluaran', function (Blueprint $table) {
            $table->id();
            $table->string('kategori');
            $table->text('keterangan');
            $table->integer('nominal');
            $table->string('bukti_url')->nullable();
            $table->date('tanggal');
            $table->string('status')->default(StatusPengeluaran::DRAFT->value);
            $table->foreignId('dibuat_oleh_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('disetujui_oleh_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('disetujui_at')->nullable();
            $table->text('catatan_review')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengeluaran');
    }
};
