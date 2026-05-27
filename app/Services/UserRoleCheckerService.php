<?php

namespace App\Services;

use App\Models\User;

/**
 * Checks user roles and restrictions.
 */
class UserRoleCheckerService
{
    /**
     * Check if the user is a user, admin, or super admin.
     *
     * @param  User $user
     *
     * @return bool
     */
    public function isUser(User $user): bool
    {
        return $user->is_user || $this->isAdmin($user);
    }
    /**
     * Check if user is admin or super admin.
     *
     * @param  User $user
     *
     * @return bool
     */
    public function isAdmin(User $user): bool
    {
        return $user->is_admin || $user->is_super_admin;
    }

    /**
     * Check if the user is a super admin.
     *
     * @param  User $user
     *
     * @return bool
     */
    public function isSuperAdmin(User $user): bool
    {
        return $user->is_super_admin;
    }

    /**
     * Check if admin is restricted from managing the target user.
     *
     * Regular admins cannot manage super admins.
     *
     * @param  User $user
     * @param  User $model
     *
     * @return bool
     */
    public function isRestrictedFromManaging(User $user, User $model): bool
    {
        return $user->is_admin
            && ! $user->is_super_admin
            && $model->is_admin;
    }
}
