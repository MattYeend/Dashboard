<?php

namespace App\Policies;

use App\Models\User;
use App\Services\Users\PolicyAuthorisationService;

class UserPolicy
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
     *
     * Only admins can view the list of users.
     */
    public function viewAny(User $user): bool
    {
        return $this->authorisationService->isAdmin($user);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, User $target): bool
    {
        return $this->authorisationService->canView($user, $target);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $this->authorisationService->isAdmin($user);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, User $target): bool
    {
        return $this->authorisationService->canUpdate($user, $target);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, User $target): bool
    {
        return $this->authorisationService->canDelete($user, $target);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, User $target): bool
    {
        return $this->authorisationService->canRestore($user, $target);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, User $target): bool
    {
        return $this->authorisationService->canForceDelete($user, $target);
    }

    /**
     * Determine whether the user can bulk delete models.
     */
    public function bulkDelete(User $user): bool
    {
        return $this->authorisationService->isAdmin($user);
    }

    /**
     * Determine whether the user can bulk restore models.
     */
    public function bulkRestore(User $user): bool
    {
        return $this->authorisationService->isAdmin($user);
    }

    /**
     * Determine whether the user can import models.
     */
    public function import(User $user): bool
    {
        return $this->authorisationService->isAdmin($user);
    }

    /**
     * Determine whether the user can export models.
     */
    public function export(User $user): bool
    {
        return $this->authorisationService->isUser($user);
    }

    /**
     * Determine whether the user can access the model.
     *
     * Alias for view, used as a secondary access gate check.
     */
    public function access(User $user, User $target): bool
    {
        return $this->authorisationService->canView($user, $target);
    }
}
