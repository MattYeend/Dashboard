<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

class UserRoleResolverService
{
    /**
     * Determine if the user has the base user role by enum or Spatie role.
     */
    public function hasUserRole(User $user): bool
    {
        return $user->hasRole('User')
            || $this->hasAdminRole($user)
            || $this->hasSuperAdminRole($user);
    }

    /**
     * Determine if the user has the admin role by enum or Spatie role.
     */
    public function hasAdminRole(User $user): bool
    {
        return $user->hasRole('Admin') || $this->hasSuperAdminRole($user);
    }

    /**
     * Determine if the user has the super admin role by enum or Spatie role.
     */
    public function hasSuperAdminRole(User $user): bool
    {
        $roleId = Role::where('name', 'Super Admin')->value('id');

        if ($roleId === null) {
            return false;
        }

        return DB::table('model_has_roles')
            ->where('model_id', $user->getKey())
            ->where('model_type', $user->getMorphClass())
            ->where('role_id', $roleId)
            ->exists();
    }
}
