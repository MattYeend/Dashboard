<?php

namespace App\Services\RegistrationInterests;

use App\Models\User;

class PolicyAuthorisationService
{
    /**
     * Determine whether the given user is an admin or super admin.
     */
    public function isAdmin(User $user): bool
    {
        return in_array($user->role, ['admin', 'super_admin'], true);
    }
}
