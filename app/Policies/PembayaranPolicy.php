<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\Pembayaran;
use App\Models\User;

class PembayaranPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * WARGA hanya boleh melihat riwayat pembayarannya sendiri.
     */
    public function view(User $user, Pembayaran $pembayaran): bool
    {
        if ($user->role === Role::WARGA) {
            return $user->warga_id === $pembayaran->warga_id;
        }

        return true;
    }

    /**
     * WARGA upload bukti bayar untuk dirinya sendiri.
     * SUPERADMIN/BENDAHARA boleh mencatat pembayaran atas nama warga.
     */
    public function create(User $user): bool
    {
        return in_array($user->role, [Role::WARGA, Role::SUPERADMIN, Role::BENDAHARA], true);
    }

    /**
     * Konfirmasi / tolak pembayaran: SUPERADMIN & BENDAHARA saja.
     */
    public function confirm(User $user, Pembayaran $pembayaran): bool
    {
        return in_array($user->role, [Role::SUPERADMIN, Role::BENDAHARA], true);
    }
}
