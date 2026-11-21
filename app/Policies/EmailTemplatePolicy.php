<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\EmailTemplate;
use App\Models\User;
use App\Services\EmailTemplates\PolicyAuthorisationService;

class EmailTemplatePolicy
{
    /**
     * Inject the required services into the policy.
     */
    public function __construct(
        protected PolicyAuthorisationService $authorisationService
    ) {}

    /**
     * Determine whether the user can view any email templates.
     */
    public function viewAny(User $user): bool
    {
        return $this->authorisationService->canViewAny($user);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, EmailTemplate $emailTemplate): bool
    {
        return $this->authorisationService->canView($user, $emailTemplate);
    }

    /**
     * Determine whether the user can create email templates.
     */
    public function create(User $user): bool
    {
        return $this->authorisationService->canCreate($user);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, EmailTemplate $emailTemplate): bool
    {
        return $this->authorisationService->canUpdate($user, $emailTemplate);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, EmailTemplate $emailTemplate): bool
    {
        return $this->authorisationService->canDelete($user, $emailTemplate);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, EmailTemplate $emailTemplate): bool
    {
        return $this->authorisationService->canRestore($user, $emailTemplate);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, EmailTemplate $emailTemplate): bool
    {
        return $this->authorisationService->canForceDelete($user, $emailTemplate);
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
     * Determine whether the user can import email templates.
     */
    public function import(User $user): bool
    {
        return $this->authorisationService->canImport($user);
    }

    /**
     * Determine whether the user can export email templates.
     */
    public function export(User $user): bool
    {
        return $this->authorisationService->canExport($user);
    }
}
