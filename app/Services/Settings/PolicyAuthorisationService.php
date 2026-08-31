<?php

namespace App\Services\Settings;

use App\Models\User;

class PolicyAuthorisationService
{
    /**
     * Determine whether the user can view the general settings group.
     */
    public function canViewGeneral(User $user): bool
    {
        return $user->can('view settings');
    }

    /**
     * Determine whether the user can update the general settings group.
     */
    public function canUpdateGeneral(User $user): bool
    {
        return $user->can('edit settings');
    }

    /**
     * Determine whether the user can view the system settings group.
     */
    public function canViewSystem(User $user): bool
    {
        return $user->can('view system settings');
    }

    /**
     * Determine whether the user can update the system settings group.
     */
    public function canUpdateSystem(User $user): bool
    {
        return $user->can('edit system settings');
    }

    /**
     * Determine whether the user can view the security settings group.
     */
    public function canViewSecurity(User $user): bool
    {
        return $user->can('view security settings');
    }

    /**
     * Determine whether the user can update the security settings group.
     */
    public function canUpdateSecurity(User $user): bool
    {
        return $user->can('edit security settings');
    }
}
