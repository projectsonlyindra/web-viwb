<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\User;

class PengaturanPolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role, [Role::SUPERADMIN, Role::KETUA_RT], true);
    }

    public function update(User $user): bool
    {
        return in_array($user->role, [Role::SUPERADMIN, Role::KETUA_RT], true);
    }
}
