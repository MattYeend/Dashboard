<?php

namespace App\Policies;

use App\Models\Label;
use App\Models\User;
use App\Services\Labels\PolicyAuthorisationService;

class LabelPolicy
{
    /**
     * Inject the required services into the policy.
     */
    public function __construct(
        protected PolicyAuthorisationService $authorisationService
    ) {}

    /**
     * Determine whether the user can view any labels.
     */
    public function viewAny(User $user): bool
    {
        return $this->authorisationService->isAdmin($user);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Label $label): bool
    {
        return $this->authorisationService->canView($user, $label);
    }

    /**
     * Determine whether the user can create labels.
     */
    public function create(User $user): bool
    {
        return $this->authorisationService->isAdmin($user);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Label $label): bool
    {
        return $this->authorisationService->canUpdate($user, $label);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Label $label): bool
    {
        return $this->authorisationService->canDelete($user, $label);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Label $label): bool
    {
        return $this->authorisationService->canRestore($user, $label);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Label $label): bool
    {
        return $this->authorisationService->canForceDelete($user, $label);
    }

    /**
     * Determine whether the user can import labels.
     */
    public function import(User $user): bool
    {
        return $this->authorisationService->canImport($user);
    }

    /**
     * Determine whether the user can export labels.
     */
    public function export(User $user): bool
    {
        return $this->authorisationService->canExport($user);
    }

    /**
     * Determine whether the user can assign the label to a record.
     */
    public function assign(User $user, Label $label): bool
    {
        return $this->authorisationService->canAssign($user, $label);
    }
}
