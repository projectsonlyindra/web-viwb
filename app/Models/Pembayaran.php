<?php

namespace App\Models;

use App\Enums\StatusPembayaran;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'warga_id', 'total_dibayar', 'bukti_url', 'catatan', 'status',
    'dikonfirmasi_oleh_id', 'dikonfirmasi_at',
])]
class Pembayaran extends Model
{
    use HasFactory;

    protected $table = 'pembayaran';

    protected function casts(): array
    {
        return [
            'status' => StatusPembayaran::class,
            'dikonfirmasi_at' => 'datetime',
        ];
    }

    public function warga(): BelongsTo
    {
        return $this->belongsTo(Warga::class);
    }

    public function dikonfirmasiOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dikonfirmasi_oleh_id');
    }

    public function item(): HasMany
    {
        return $this->hasMany(PembayaranItem::class);
    }
}
