<?php

namespace App\Services\Reports;

use App\Models\Report;
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
     * Check if report is active (not soft-deleted).
     */
    public function isActive(Report $report): bool
    {
        return ! $report->trashed();
    }

    /**
     * Check if report is soft-deleted.
     */
    public function isTrashed(Report $report): bool
    {
        return $report->trashed();
    }

    /**
     * Check if report is active (not soft-deleted) and can be modified.
     */
    public function canBeModified(Report $report): bool
    {
        return $this->isActive($report);
    }

    /**
     * Check if report is soft-deleted and can be restored or force deleted.
     */
    public function canBeRestoredOrForceDeleted(Report $report): bool
    {
        return $this->isTrashed($report);
    }

    /**
     * Check if user can perform the given action on the report based on its active status.
     */
    public function canUserPerformAction(
        User $actor,
        string $action,
        Report $target
    ): bool {
        return match ($action) {
            'modify' => $this->roleChecker->isAdmin($actor) && $this->canBeModified($target),
            'restoreOrForceDelete' => $this->roleChecker->isAdmin($actor) && $this->canBeRestoredOrForceDeleted($target),
            default => false,
        };
    }
}
