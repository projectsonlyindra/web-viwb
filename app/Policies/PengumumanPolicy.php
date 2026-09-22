<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\Pengumuman;
use App\Models\User;

class PengumumanPolicy
{
    /**
     * Semua role (termasuk WARGA) boleh melihat pengumuman.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Pengumuman $pengumuman): bool
    {
        return true;
    }

    /**
     * Buat pengumuman & blast WA: SUPERADMIN & KETUA_RT.
     */
    public function create(User $user): bool
    {
        return in_array($user->role, [Role::SUPERADMIN, Role::KETUA_RT], true);
    }

    public function update(User $user, Pengumuman $pengumuman): bool
    {
        return in_array($user->role, [Role::SUPERADMIN, Role::KETUA_RT], true);
    }

    public function delete(User $user, Pengumuman $pengumuman): bool
    {
        return in_array($user->role, [Role::SUPERADMIN, Role::KETUA_RT], true);
    }
}
