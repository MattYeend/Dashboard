<?php

namespace App\Policies;

use App\Models\User;
use App\Services\Users\PolicyAuthorisationService;

class UserPolicy
{
    /**
     * The authorisation service handling permission checks.
     *
     * @var PolicyAuthorisationService
     */
    protected PolicyAuthorisationService $authorisationService;

    /**
     * Inject the required service into the policy.
     *
     * @param  PolicyAuthorisationService $authorisationService
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
     *
     * @param  User $user
     *
     * @return bool
     */
    public function viewAny(User $user): bool
    {
        return $this->authorisationService->isAdmin($user);
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  User $user
     * @param  User $target
     *
     * @return bool
     */
    public function view(User $user, User $target): bool
    {
        return $this->authorisationService->canView($user, $target);
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  User $user
     *
     * @return bool
     */
    public function create(User $user): bool
    {
        return $this->authorisationService->isAdmin($user);
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  User $user
     * @param  User $target
     *
     * @return bool
     */
    public function update(User $user, User $target): bool
    {
        return $this->authorisationService->canUpdate($user, $target);
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  User $user
     * @param  User $target
     *
     * @return bool
     */
    public function delete(User $user, User $target): bool
    {
        return $this->authorisationService->canDelete($user, $target);
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  User $user
     * @param  User $target
     *
     * @return bool
     */
    public function restore(User $user, User $target): bool
    {
        return $this->authorisationService->canRestore($user, $target);
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  User $user
     * @param  User $target
     *
     * @return bool
     */
    public function forceDelete(User $user, User $target): bool
    {
        return $this->authorisationService->canForceDelete($user, $target);
    }

    /**
     * Determine whether the user can bulk delete models.
     *
     * @param  User $user
     *
     * @return bool
     */
    public function bulkDelete(User $user): bool
    {
        return $this->authorisationService->isAdmin($user);
    }

    /**
     * Determine whether the user can bulk restore models.
     *
     * @param  User $user
     *
     * @return bool
     */
    public function bulkRestore(User $user): bool
    {
        return $this->authorisationService->isAdmin($user);
    }

    /**
     * Determine whether the user can import models.
     *
     * @param  User $user
     *
     * @return bool
     */
    public function import(User $user): bool
    {
        return $this->authorisationService->isAdmin($user);
    }

    /**
     * Determine whether the user can export models.
     *
     * @param  User $user
     *
     * @return bool
     */
    public function export(User $user): bool
    {
        return $this->authorisationService->isUser($user);
    }

    /**
     * Determine whether the user can access the model.
     *
     * Alias for view, used as a secondary access gate check.
     *
     * @param  User $user
     * @param  User $target
     *
     * @return bool
     */
    public function access(User $user, User $target): bool
    {
        return $this->authorisationService->canView($user, $target);
    }
}
