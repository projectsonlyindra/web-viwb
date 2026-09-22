<?php

use App\Enums\StatusBroadcastJob;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('broadcast_job', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pengumuman_id')->nullable()->constrained('pengumuman')->nullOnDelete();
            $table->string('no_wa');
            $table->foreignId('warga_id')->nullable()->constrained('warga')->nullOnDelete();
            $table->text('pesan');
            $table->string('jenis');
            $table->string('status')->default(StatusBroadcastJob::PENDING->value);
            $table->integer('attempts')->default(0);
            $table->text('error_msg')->nullable();
            $table->timestamp('scheduled_at');
            $table->timestamp('processed_at')->nullable();
            $table->timestamp('created_at')->nullable();

            $table->index(['status', 'scheduled_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('broadcast_job');
    }
};
