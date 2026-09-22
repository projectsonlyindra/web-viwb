<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'pengumuman_id', 'no_wa', 'warga_id', 'pesan', 'jenis',
    'status', 'error_msg', 'sent_at',
])]
class BroadcastLog extends Model
{
    use HasFactory;

    protected $table = 'broadcast_log';

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'sent_at' => 'datetime',
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
