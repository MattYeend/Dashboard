<?php

namespace App\Services\Companies;

use App\Models\Company;
use App\Models\User;
use App\Services\UserRoleCheckerService;

class ActiveCheckerService
{
    /**
     * Inject the required services into the active checker service.
     */
    public function __construct(
        protected readonly UserRoleCheckerService $roleChecker
    ) {}

    /**
     * Check if company is active (not soft-deleted).
     */
    public function isActive(
        Company $company
    ): bool {
        return ! $company->trashed();
    }

    /**
     * Check if company is soft-deleted.
     */
    public function isTrashed(
        Company $company
    ): bool {
        return $company->trashed();
    }

    /**
     * Check if company is active and can be updated or deleted.
     */
    public function canBeModified(
        Company $company
    ): bool {
        return $this->isActive(
            $company
        );
    }

    /**
     * Check if company is soft-deleted and can be restored or force-deleted.
     */
    public function canBeRestoredOrForceDeleted(
        Company $company
    ): bool {
        return $this->isTrashed(
            $company
        );
    }

    /**
     * Check if the user can perform an action on the company.
     */
    public function canUserPerformAction(
        User $actor,
        string $action,
        Company $target
    ): bool {
        return match ($action) {
            'modify' => $this->roleChecker->isAdmin($actor) && $this->canBeModified($target),
            'restoreOrForceDelete' => $this->roleChecker->isAdmin($actor) && $this->canBeRestoredOrForceDeleted($target),
            default => false,
        };
    }
}
