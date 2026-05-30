<?php

namespace App\Services;

use App\Models\User;

class UserRoleResolverService
{
    /**
     * Determine if the user has the base user role by enum or Spatie role.
     */
    public function hasUserRole(User $user): bool
    {
        return $user->is_user || $user->hasRole('User');
    }

    /**
     * Determine if the user has the admin role by enum or Spatie role.
     */
    public function hasAdminRole(User $user): bool
    {
        return $user->is_admin || $user->hasRole('Admin');
    }

    /**
     * Determine if the user has the super admin role by enum or Spatie role.
     */
    public function hasSuperAdminRole(User $user): bool
    {
        return $user->is_super_admin || $user->hasRole('Super Admin');
    }
}
