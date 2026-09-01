<?php

namespace App\Policies;

use App\Models\Log;
use App\Models\User;
use App\Services\Logs\PolicyAuthorisationService;

class LogPolicy
{
    /**
     * Inject the required services into the policy.
     */
    public function __construct(
        protected readonly PolicyAuthorisationService $authorisation
    ) {}

    /**
     * Determine whether the user can view any activity logs.
     */
    public function viewAny(User $user): bool
    {
        return $this->authorisation->canViewAny($user);
    }

    /**
     * Determine whether the user can view the activity log.
     */
    public function view(User $user, Log $log): bool
    {
        return $this->authorisation->canView($user, $log);
    }

    /**
     * Determine whether the user can delete the activity log.
     */
    public function delete(User $user, Log $log): bool
    {
        return $this->authorisation->canDelete($user, $log);
    }

    /**
     * Determine whether the user can export activity logs.
     */
    public function export(User $user): bool
    {
        return $this->authorisation->canExport($user);
    }

    /**
     * Determine whether the user can bulk delete activity logs.
     */
    public function bulkDelete(User $user): bool
    {
        return $this->authorisation->isAdmin($user);
    }
}
