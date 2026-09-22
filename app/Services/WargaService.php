<?php

namespace App\Services;

use App\Models\Warga;
use Illuminate\Database\Eloquent\Collection;

class WargaService
{
    public function getList(array $filters = []): Collection
    {
        return Warga::query()
            ->when(
                $filters['blok'] ?? null,
                fn ($query, $blok) => $query->where('unit_id', 'like', "{$blok}%")
            )
            ->when(
                $filters['status'] ?? null,
                fn ($query, $status) => $query->where('status_warga', $status)
            )
            ->orderBy('unit_id')
            ->get();
    }

    public function create(array $data): Warga
    {
        return Warga::query()->create($data);
    }

    public function update(Warga $warga, array $data): Warga
    {
        $warga->update($data);

        return $warga;
    }

    public function delete(Warga $warga): void
    {
        $warga->delete();
    }
}
