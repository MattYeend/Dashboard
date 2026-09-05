<?php

namespace App\Policies;

use App\Models\Report;
use App\Models\User;
use App\Services\Reports\PolicyAuthorisationService;

class ReportPolicy
{
    /**
     * Inject the required services into the policy.
     */
    public function __construct(
        protected PolicyAuthorisationService $authorisationService
    ) {}

    /**
     * Determine whether the user can view any reports.
     */
    public function viewAny(User $user): bool
    {
        return $this->authorisationService->canViewAny($user);
    }

    /**
     * Determine whether the user can view the given report.
     */
    public function view(User $user, Report $report): bool
    {
        return $this->authorisationService->canView($user, $report);
    }

    /**
     * Determine whether the user can create a report.
     */
    public function create(User $user): bool
    {
        return $this->authorisationService->canCreate($user);
    }

    /**
     * Determine whether the user can update the given report.
     */
    public function update(User $user, Report $report): bool
    {
        return $this->authorisationService->canUpdate($user, $report);
    }

    /**
     * Determine whether the user can delete the given report.
     */
    public function delete(User $user, Report $report): bool
    {
        return $this->authorisationService->canDelete($user, $report);
    }

    /**
     * Determine whether the user can restore the given report.
     */
    public function restore(User $user, Report $report): bool
    {
        return $this->authorisationService->canRestore($user, $report);
    }

    /**
     * Determine whether the user can permanently delete the given report.
     */
    public function forceDelete(User $user, Report $report): bool
    {
        return $this->authorisationService->canForceDelete($user, $report);
    }

    /**
     * Determine whether the user can import reports.
     */
    public function import(User $user): bool
    {
        return $this->authorisationService->canImport($user);
    }

    /**
     * Determine whether the user can export reports.
     */
    public function export(User $user): bool
    {
        return $this->authorisationService->canExport($user);
    }

    /**
     * Determine whether the user can schedule reports to run automatically.
     */
    public function schedule(User $user): bool
    {
        return $this->authorisationService->canSchedule($user);
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
}
