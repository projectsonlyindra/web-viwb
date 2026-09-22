<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('broadcast_log', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pengumuman_id')->nullable()->constrained('pengumuman')->nullOnDelete();
            $table->string('no_wa');
            $table->foreignId('warga_id')->nullable()->constrained('warga')->nullOnDelete();
            $table->text('pesan');
            $table->string('jenis');
            $table->string('status');
            $table->text('error_msg')->nullable();
            $table->timestamp('sent_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('broadcast_log');
    }
};
