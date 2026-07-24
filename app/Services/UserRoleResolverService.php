<?php

namespace App\Services;

use App\Models\User;

class UserRoleResolverService
{
    /**
     * Determine if the user has the base user role by enum or Spatie role.
     */
    public function hasUserRole(
        User $user
    ): bool {
        return $user->hasRole('User')
            || $this->hasAdminRole($user)
            || $this->hasSuperAdminRole($user);
    }

    /**
     * Determine if the user has the admin role by enum or Spatie role.
     */
    public function hasAdminRole(
        User $user
    ): bool {
        return $user->hasRole('Admin')
            || $this->hasSuperAdminRole($user);
    }

    /**
     * Determine if the user has the super admin role by enum or Spatie role.
     */
    public function hasSuperAdminRole(
        User $user
    ): bool {
        return $user->hasRole(
            'Super Admin'
        );
    }
}
