<?php

namespace App\Policies;

use App\Models\Permission;
use App\Models\User;
use App\Services\Permissions\PolicyAuthorisationService;

class PermissionPolicy
{
    /**
     * The authorisation service handling permission checks.
     */
    protected PolicyAuthorisationService $authorisationService;

    /**
     * Inject the required service into the policy.
     */
    public function __construct(
        PolicyAuthorisationService $authorisationService
    ) {
        $this->authorisationService = $authorisationService;
    }

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(
        User $user
    ): bool {
        return $this->authorisationService->canViewAny(
            $user
        );
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(
        User $user,
        Permission $permission
    ): bool {
        return $this->authorisationService->canView(
            $user,
            $permission
        );
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(
        User $user
    ): bool {
        return $this->authorisationService->canCreate(
            $user
        );
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(
        User $user,
        Permission $permission
    ): bool {
        return $this->authorisationService->canUpdate(
            $user,
            $permission
        );
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(
        User $user,
        Permission $permission
    ): bool {
        return $this->authorisationService->canDelete(
            $user,
            $permission
        );
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(
        User $user,
        Permission $permission
    ): bool {
        return $this->authorisationService->canRestore(
            $user,
            $permission
        );
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Permission $permission): bool
    {
        return $this->authorisationService->canForceDelete(
            $user,
            $permission
        );
    }

    /**
     * Determine whether the user can assign roles to the model.
     */
    public function assign(
        User $user,
        Permission $permission
    ): bool {
        return $this->authorisationService->canAssign(
            $user,
            $permission
        );
    }

    /**
     * Determine whether the user can bulk delete models.
     */
    public function bulkDelete(
        User $user
    ): bool {
        return $this->authorisationService->isAdmin(
            $user
        );
    }

    /**
     * Determine whether the user can bulk restore models.
     */
    public function bulkRestore(
        User $user
    ): bool {
        return $this->authorisationService->isAdmin(
            $user
        );
    }
}
