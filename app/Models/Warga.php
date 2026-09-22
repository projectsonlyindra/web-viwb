<?php

namespace App\Models;

use App\Enums\JenisKendaraan;
use App\Enums\StatusWarga;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'nik', 'nama', 'no_wa', 'unit_id', 'jenis_kendaraan',
    'status_warga', 'ikut_hippam', 'ikut_kebersihan',
])]
class Warga extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'warga';

    protected function casts(): array
    {
        return [
            'jenis_kendaraan' => JenisKendaraan::class,
            'status_warga' => StatusWarga::class,
            'ikut_hippam' => 'boolean',
            'ikut_kebersihan' => 'boolean',
        ];
    }

    public function user(): HasOne
    {
        return $this->hasOne(User::class);
    }

    public function tagihan(): HasMany
    {
        return $this->hasMany(Tagihan::class);
    }

    public function pembayaran(): HasMany
    {
        return $this->hasMany(Pembayaran::class);
    }
}
