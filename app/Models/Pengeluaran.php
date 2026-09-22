<?php

namespace App\Models;

use App\Enums\StatusPengeluaran;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'kategori', 'keterangan', 'nominal', 'bukti_url', 'tanggal', 'status',
    'dibuat_oleh_id', 'disetujui_oleh_id', 'disetujui_at', 'catatan_review',
])]
class Pengeluaran extends Model
{
    use HasFactory;

    protected $table = 'pengeluaran';

    protected function casts(): array
    {
        return [
            'status' => StatusPengeluaran::class,
            'tanggal' => 'date',
            'disetujui_at' => 'datetime',
        ];
    }

    public function dibuatOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dibuat_oleh_id');
    }

    public function disetujuiOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'disetujui_oleh_id');
    }
}
