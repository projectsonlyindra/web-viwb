<?php

namespace App\Models;

use App\Enums\StatusBroadcastJob;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'pengumuman_id', 'no_wa', 'warga_id', 'pesan', 'jenis', 'status',
    'attempts', 'error_msg', 'scheduled_at', 'processed_at',
])]
class BroadcastJob extends Model
{
    use HasFactory;

    protected $table = 'broadcast_job';

    const UPDATED_AT = null;

    protected function casts(): array
    {
        return [
            'status' => StatusBroadcastJob::class,
            'scheduled_at' => 'datetime',
            'processed_at' => 'datetime',
        ];
    }

    public function pengumuman(): BelongsTo
    {
        return $this->belongsTo(Pengumuman::class);
    }

    public function warga(): BelongsTo
    {
        return $this->belongsTo(Warga::class);
    }
}
