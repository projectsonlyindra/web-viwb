<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\User;
use App\Models\Warga;

class WargaPolicy
{
    /**
     * SUPERADMIN, KETUA_RT, BENDAHARA, TIM_DIVISI semua boleh melihat daftar warga
     * (butuh untuk kebutuhan masing-masing: laporan, penagihan, alert tunggakan).
     * WARGA tidak melihat daftar warga lain.
     */
    public function viewAny(User $user): bool
    {
        return $user->role !== Role::WARGA;
    }

    /**
     * WARGA hanya boleh melihat data dirinya sendiri.
     */
    public function view(User $user, Warga $warga): bool
    {
        if ($user->role === Role::WARGA) {
            return $user->warga_id === $warga->id;
        }

        return true;
    }

    /**
     * Kelola master data warga: SUPERADMIN saja.
     */
    public function create(User $user): bool
    {
        return $user->role === Role::SUPERADMIN;
    }

    public function update(User $user, Warga $warga): bool
    {
        return $user->role === Role::SUPERADMIN;
    }

    public function delete(User $user, Warga $warga): bool
    {
        return $user->role === Role::SUPERADMIN;
    }
}
