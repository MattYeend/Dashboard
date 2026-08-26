<?php

namespace App\Policies;

use App\Models\Deal;
use App\Models\User;
use App\Services\Deals\PolicyAuthorisationService;

class DealPolicy
{
    /**
     * Inject the required services into the policy.
     */
    public function __construct(
        protected PolicyAuthorisationService $authorisationService
    ) {}

    /**
     * Determine whether the user can view any deals.
     */
    public function viewAny(User $user): bool
    {
        return $this->authorisationService->canViewAny($user);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Deal $deal): bool
    {
        return $this->authorisationService->canView($user, $deal);
    }

    /**
     * Determine whether the user can create deals.
     */
    public function create(User $user): bool
    {
        return $this->authorisationService->isAdmin($user);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Deal $deal): bool
    {
        return $this->authorisationService->canUpdate($user, $deal);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Deal $deal): bool
    {
        return $this->authorisationService->canDelete($user, $deal);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Deal $deal): bool
    {
        return $this->authorisationService->canRestore($user, $deal);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Deal $deal): bool
    {
        return $this->authorisationService->canForceDelete($user, $deal);
    }

    /**
     * Determine whether the user can import deals.
     */
    public function import(User $user): bool
    {
        return $this->authorisationService->canImport($user);
    }

    /**
     * Determine whether the user can export deals.
     */
    public function export(User $user): bool
    {
        return $this->authorisationService->canExport($user);
    }

    /**
     * Determine whether the user can assign the deal to another user.
     */
    public function assign(User $user, Deal $deal): bool
    {
        return $this->authorisationService->canAssign($user, $deal);
    }

    /**
     * Determine whether the user can change the status of the deal.
     */
    public function changeStatus(User $user, Deal $deal): bool
    {
        return $this->authorisationService->canChangeStatus($user, $deal);
    }
}
