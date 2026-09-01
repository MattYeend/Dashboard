<?php

namespace App\Services\Logs;

use App\Models\Log;
use App\Models\User;
use App\Services\UserRoleCheckerService;

class PolicyAuthorisationService
{
    /**
     * Inject the required services into the policy authorisation service.
     */
    public function __construct(
        protected readonly UserRoleCheckerService $roleChecker
    ) {}

    /**
     * Check if user is a regular user, admin, or super admin.
     */
    public function isUser(User $user): bool
    {
        return $this->roleChecker->isUser($user);
    }

    /**
     * Check if user is admin or super admin.
     */
    public function isAdmin(User $user): bool
    {
        return $this->roleChecker->isAdmin($user);
    }

    /**
     * Determine whether the user can view any activity logs.
     */
    public function canViewAny(User $actor): bool
    {
        return $actor->can('view any activity logs');
    }

    /**
     * Determine whether the user can view the activity log.
     */
    public function canView(User $actor, Log $target): bool
    {
        return $actor->can('view any activity logs');
    }

    /**
     * Determine whether the user can delete the activity log.
     */
    public function canDelete(User $actor, Log $target): bool
    {
        return $actor->can('delete activity logs');
    }

    /**
     * Determine whether the user can export activity logs.
     */
    public function canExport(User $actor): bool
    {
        return $actor->can('export activity logs');
    }
}
