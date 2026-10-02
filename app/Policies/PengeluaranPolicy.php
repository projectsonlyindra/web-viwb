<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\Pengaturan;
use App\Models\Pengeluaran;
use App\Models\User;

class PengeluaranPolicy
{
    public function viewAny(User $user): bool
    {
        if (in_array($user->role, [Role::SUPERADMIN, Role::KETUA_RT, Role::BENDAHARA], true)) {
            return true;
        }

        return (bool) Pengaturan::get('laporan_terbuka_ke_warga', false);
    }

    public function view(User $user, Pengeluaran $pengeluaran): bool
    {
        if (in_array($user->role, [Role::SUPERADMIN, Role::KETUA_RT, Role::BENDAHARA], true)) {
            return true;
        }

        return (bool) Pengaturan::get('laporan_terbuka_ke_warga', false);
    }

    /**
     * Entri pengeluaran: SUPERADMIN & BENDAHARA.
     * KETUA_RT tidak bisa entri, hanya approve/reject.
     */
    public function create(User $user): bool
    {
        return in_array($user->role, [Role::SUPERADMIN, Role::BENDAHARA], true);
    }

    /**
     * Approve/reject: SUPERADMIN & KETUA_RT.
     * Tidak boleh approve pengeluaran buatan sendiri (berlaku juga untuk SUPERADMIN
     * yang entri manual lewat alur approval, bukan lewat auto-approve saat create).
     */
    public function approve(User $user, Pengeluaran $pengeluaran): bool
    {
        if ($user->id === $pengeluaran->dibuat_oleh_id) {
            return false;
        }

        return in_array($user->role, [Role::SUPERADMIN, Role::KETUA_RT], true);
    }
}
