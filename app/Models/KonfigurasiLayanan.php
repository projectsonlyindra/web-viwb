<?php

namespace App\Models;

use App\Enums\JenisTagihan;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'jenis', 'nominal', 'nominal_mobil', 'nominal_tanpa_mobil',
    'cutoff_hari', 'denda_harian', 'denda_maksimal', 'alert_tunggakan_bulan',
    'tarif_per_m3', 'minimal_m3', 'minimal_nominal',
])]
class KonfigurasiLayanan extends Model
{
    use HasFactory;

    protected $table = 'konfigurasi_layanan';

    protected function casts(): array
    {
        return [
            'jenis' => JenisTagihan::class,
        ];
    }
}
