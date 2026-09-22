<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\Tagihan;
use App\Models\User;

class TagihanPolicy
{
    /**
     * Semua role staff boleh melihat daftar tagihan (dashboard, laporan).
     * WARGA hanya lewat halaman tagihan miliknya sendiri (lihat view()).
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * WARGA hanya boleh melihat tagihan miliknya sendiri.
     * TIM_DIVISI hanya boleh melihat tagihan sesuai jenis layanan divisinya.
     */
    public function view(User $user, Tagihan $tagihan): bool
    {
        if ($user->role === Role::WARGA) {
            return $user->warga_id === $tagihan->warga_id;
        }

        if ($user->role === Role::TIM_DIVISI) {
            return $user->divisi?->value === $tagihan->jenis->value;
        }

        return true;
    }

    /**
     * Generate tagihan bulanan: SUPERADMIN & BENDAHARA.
     */
    public function create(User $user): bool
    {
        return in_array($user->role, [Role::SUPERADMIN, Role::BENDAHARA], true);
    }
}
