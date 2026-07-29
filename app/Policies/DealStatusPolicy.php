<?php

namespace App\Policies;

use App\Models\DealStatus;
use App\Models\User;
use App\Services\DealStatuses\PolicyAuthorisationService;

class DealStatusPolicy
{
    /**
     * Inject the required services into the policy.
     */
    public function __construct(
        protected PolicyAuthorisationService $authorisationService
    ) {}

    /**
     * Determine whether the user can view any deal statuses.
     */
    public function viewAny(User $user): bool
    {
        return $this->authorisationService->canViewAny($user);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, DealStatus $dealStatus): bool
    {
        return $this->authorisationService->canView($user, $dealStatus);
    }

    /**
     * Determine whether the user can create deal statuses.
     */
    public function create(User $user): bool
    {
        return $this->authorisationService->canCreate($user);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, DealStatus $dealStatus): bool
    {
        return $this->authorisationService->canUpdate($user, $dealStatus);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, DealStatus $dealStatus): bool
    {
        return $this->authorisationService->canDelete($user, $dealStatus);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, DealStatus $dealStatus): bool
    {
        return $this->authorisationService->canRestore($user, $dealStatus);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, DealStatus $dealStatus): bool
    {
        return $this->authorisationService->canForceDelete($user, $dealStatus);
    }

    /**
     * Determine whether the user can import deal statuses.
     */
    public function import(User $user): bool
    {
        return $this->authorisationService->canImport($user);
    }

    /**
     * Determine whether the user can export deal statuses.
     */
    public function export(User $user): bool
    {
        return $this->authorisationService->canExport($user);
    }

    /**
     * Determine whether the user can assign the deal status.
     */
    public function assign(User $user, DealStatus $dealStatus): bool
    {
        return $this->authorisationService->canAssign($user, $dealStatus);
    }
}
