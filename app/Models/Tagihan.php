<?php

namespace App\Models;

use App\Enums\JenisTagihan;
use App\Enums\StatusTagihan;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'warga_id', 'jenis', 'periode', 'nominal', 'status',
    'cutoff_hari', 'denda_harian', 'denda_maksimal', 'tanggal_jatuh_tempo',
    'meter_awal', 'meter_akhir', 'pemakaian_m3',
])]
class Tagihan extends Model
{
    use HasFactory;

    protected $table = 'tagihan';

    protected function casts(): array
    {
        return [
            'jenis' => JenisTagihan::class,
            'status' => StatusTagihan::class,
            'tanggal_jatuh_tempo' => 'date',
        ];
    }

    public function warga(): BelongsTo
    {
        return $this->belongsTo(Warga::class);
    }

    public function pembayaranItem(): HasMany
    {
        return $this->hasMany(PembayaranItem::class);
    }
}
