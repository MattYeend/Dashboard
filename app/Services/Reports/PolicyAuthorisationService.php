<?php

namespace App\Services\Reports;

use App\Models\Report;
use App\Models\User;
use App\Services\UserRoleCheckerService;

class PolicyAuthorisationService
{
    /**
     * Inject the required services into the policy authorisation service.
     */
    public function __construct(
        protected readonly ActiveCheckerService $activeChecker,
        protected readonly UserRoleCheckerService $roleChecker,
    ) {}

    /**
     * Check if user is admin or super admin.
     */
    public function isAdmin(User $user): bool
    {
        return $this->roleChecker->isAdmin($user);
    }

    /**
     * Check if user is a regular user, admin, or super admin.
     */
    public function isUser(User $user): bool
    {
        return $this->roleChecker->isUser($user);
    }

    /**
     * Determine whether the user can view any reports.
     */
    public function canViewAny(User $actor): bool
    {
        return $actor->can('view reports');
    }

    /**
     * Determine whether the user can view the given report.
     */
    public function canView(User $actor, Report $report): bool
    {
        if ($this->targetOutranksActor($actor, $report)) {
            return false;
        }

        return $actor->can('view reports') && $this->activeChecker->isActive($report);
    }

    /**
     * Determine whether the user can create a report.
     */
    public function canCreate(User $actor): bool
    {
        return $actor->can('create reports');
    }

    /**
     * Determine whether the user can update the given report.
     */
    public function canUpdate(User $actor, Report $report): bool
    {
        if ($this->targetOutranksActor($actor, $report)) {
            return false;
        }

        return $actor->can('edit reports') && $this->activeChecker->canBeModified($report);
    }

    /**
     * Determine whether the user can delete the given report.
     */
    public function canDelete(User $actor, Report $report): bool
    {
        if ($this->targetOutranksActor($actor, $report)) {
            return false;
        }

        return $actor->can('delete reports') && $this->activeChecker->canBeModified($report);
    }

    /**
     * Determine whether the user can restore the given report.
     *
     * There is no separate 'restore reports' permission, so this shares
     * the 'delete reports' permission - a user who can delete a report
     * can also undo that deletion.
     */
    public function canRestore(User $actor, Report $report): bool
    {
        if ($this->targetOutranksActor($actor, $report)) {
            return false;
        }

        return $actor->can('delete reports') && $this->activeChecker->canBeRestoredOrForceDeleted($report);
    }

    /**
     * Determine whether the user can permanently delete the given report.
     *
     * There is no separate 'force delete reports' permission, so this
     * relies on admin status plus the report being in a trashed state.
     */
    public function canForceDelete(User $actor, Report $report): bool
    {
        if ($this->targetOutranksActor($actor, $report)) {
            return false;
        }

        return $this->activeChecker->canUserPerformAction($actor, 'restoreOrForceDelete', $report);
    }

    /**
     * Determine whether the user can import reports.
     *
     * There is no separate 'import reports' permission, so this shares
     * the 'create reports' permission - importing is a bulk-create
     * operation.
     */
    public function canImport(User $actor): bool
    {
        return $actor->can('create reports');
    }

    /**
     * Determine whether the user can export reports.
     */
    public function canExport(User $actor): bool
    {
        return $actor->can('export reports');
    }

    /**
     * Determine whether the user can schedule reports to run automatically.
     */
    public function canSchedule(User $actor): bool
    {
        return $actor->can('schedule reports');
    }

    /**
     * Determine whether the report was created by a user who outranks the actor.
     *
     * Prevents admins from modifying reports created by super admins.
     */
    private function targetOutranksActor(User $actor, Report $target): bool
    {
        if ($this->roleChecker->isSuperAdmin($actor)) {
            return false;
        }

        $creator = $target->creator;

        if (! $creator instanceof User) {
            return false;
        }

        return $this->roleChecker->isSuperAdmin($creator);
    }
}
