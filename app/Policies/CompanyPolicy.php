<?php

namespace App\Policies;

use App\Models\Company;
use App\Models\User;
use App\Services\Companies\PolicyAuthorisationService;

class CompanyPolicy
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
        return $this->authorisationService->isAdmin(
            $user
        );
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(
        User $user,
        Company $company
    ): bool {
        return $this->authorisationService->canView(
            $user,
            $company
        );
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(
        User $user
    ): bool {
        return $this->authorisationService->isAdmin(
            $user
        );
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(
        User $user,
        Company $company
    ): bool {
        return $this->authorisationService->canUpdate(
            $user,
            $company
        );
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(
        User $user,
        Company $company
    ): bool {
        return $this->authorisationService->canDelete(
            $user,
            $company
        );
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(
        User $user,
        Company $company
    ): bool {
        return $this->authorisationService->canRestore(
            $user,
            $company);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(
        User $user,
        Company $company
    ): bool {
        return $this->authorisationService->canForceDelete(
            $user,
            $company
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

    /**
     * Determine whether the user can import models.
     */
    public function import(
        User $user
    ): bool {
        return $this->authorisationService->isAdmin(
            $user
        );
    }

    /**
     * Determine whether the user can export models.
     */
    public function export(
        User $user
    ): bool {
        return $this->authorisationService->isUser(
            $user
        );
    }

    /**
     * Determine whether the user can change the company's industry.
     */
    public function changeIndustry(
        User $user,
        Company $company
    ): bool {
        return $this->authorisationService->canChangeIndustry(
            $user,
            $company
        );
    }
}
