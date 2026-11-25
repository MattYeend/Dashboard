<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\EmailSuppression;
use App\Models\User;
use App\Services\EmailSuppressions\PolicyAuthorisationService;

class EmailSuppressionPolicy
{
    /**
     * Inject the required services into the policy.
     */
    public function __construct(
        protected PolicyAuthorisationService $authorisationService
    ) {}

    /**
     * Determine whether the user can view any email suppressions.
     */
    public function viewAny(User $user): bool
    {
        return $this->authorisationService->canViewAny($user);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, EmailSuppression $emailSuppression): bool
    {
        return $this->authorisationService->canView($user, $emailSuppression);
    }

    /**
     * Determine whether the user can create email suppressions.
     */
    public function create(User $user): bool
    {
        return $this->authorisationService->canCreate($user);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, EmailSuppression $emailSuppression): bool
    {
        return $this->authorisationService->canUpdate($user, $emailSuppression);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, EmailSuppression $emailSuppression): bool
    {
        return $this->authorisationService->canDelete($user, $emailSuppression);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, EmailSuppression $emailSuppression): bool
    {
        return $this->authorisationService->canRestore($user, $emailSuppression);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, EmailSuppression $emailSuppression): bool
    {
        return $this->authorisationService->canForceDelete($user, $emailSuppression);
    }

    /**
     * Determine whether the user can bulk delete email suppressions.
     */
    public function bulkDelete(User $user): bool
    {
        return $this->authorisationService->isAdmin($user);
    }

    /**
     * Determine whether the user can bulk restore email suppressions.
     */
    public function bulkRestore(User $user): bool
    {
        return $this->authorisationService->isAdmin($user);
    }

    /**
     * Determine whether the user can import email suppressions.
     */
    public function import(User $user): bool
    {
        return $this->authorisationService->canImport($user);
    }

    /**
     * Determine whether the user can export email suppressions.
     */
    public function export(User $user): bool
    {
        return $this->authorisationService->canExport($user);
    }
}