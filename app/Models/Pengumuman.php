<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['judul', 'isi', 'target_blok', 'lampiran'])]
class Pengumuman extends Model
{
    use HasFactory;

    protected $table = 'pengumuman';

    public function broadcastLog(): HasMany
    {
        return $this->hasMany(BroadcastLog::class);
    }

    public function broadcastJob(): HasMany
    {
        return $this->hasMany(BroadcastJob::class);
    }
}
