<?php

use App\Enums\StatusPembayaran;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pembayaran', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warga_id')->constrained('warga')->cascadeOnDelete();
            $table->integer('total_dibayar');
            $table->string('bukti_url')->nullable();
            $table->text('catatan')->nullable();
            $table->string('status')->default(StatusPembayaran::MENUNGGU_KONFIRMASI->value);
            $table->foreignId('dikonfirmasi_oleh_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('dikonfirmasi_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pembayaran');
    }
};
