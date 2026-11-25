<?php

declare(strict_types=1);

namespace App\Services\EmailSuppressions;

use App\Models\EmailSuppression;
use App\Models\User;
use App\Services\Concerns\ChecksOrganisationBoundary;
use App\Services\UserRoleCheckerService;

class PolicyAuthorisationService
{
    use ChecksOrganisationBoundary;

    /**
     * Inject the required services into the policy authorisation service.
     */
    public function __construct(
        protected readonly ActiveCheckerService $activeChecker,
        protected readonly UserRoleCheckerService $roleChecker
    ) {}

    /**
     * Check if user is a regular user, admin, or super admin.
     */
    public function isUser(User $user): bool
    {
        return $this->roleChecker->isUser($user);
    }

    /**
     * Check if user is admin or super admin.
     */
    public function isAdmin(User $user): bool
    {
        return $this->roleChecker->isAdmin($user);
    }

    /**
     * Check if email suppression is active (not soft-deleted).
     */
    public function isActive(EmailSuppression $emailSuppression): bool
    {
        return $this->activeChecker->isActive($emailSuppression);
    }

    /**
     * Check if email suppression is soft-deleted.
     */
    public function isTrashed(EmailSuppression $emailSuppression): bool
    {
        return $this->activeChecker->isTrashed($emailSuppression);
    }

    /**
     * Determine whether the user can view any email suppressions.
     */
    public function canViewAny(User $actor): bool
    {
        return $actor->can('view any email suppression');
    }

    /**
     * Determine whether the user can create email suppressions.
     */
    public function canCreate(User $actor): bool
    {
        return $actor->can('create email suppression');
    }

    /**
     * Determine whether the user can view the email suppression.
     */
    public function canView(User $actor, EmailSuppression $target): bool
    {
        if (! $this->belongsToCurrentOrganisation($target) || $this->targetOutranksActor($actor, $target)) {
            return false;
        }

        return $actor->can('view email suppression')
            && $this->activeChecker->isActive($target);
    }

    /**
     * Determine whether the user can update the email suppression.
     */
    public function canUpdate(User $actor, EmailSuppression $target): bool
    {
        if (! $this->belongsToCurrentOrganisation($target) || $this->targetOutranksActor($actor, $target)) {
            return false;
        }

        return $actor->can('edit email suppression')
            && $this->activeChecker->isEditable($target);
    }

    /**
     * Determine whether the user can delete the email suppression.
     *
     * Deleting re-subscribes the address, so this is an Admin only action.
     */
    public function canDelete(User $actor, EmailSuppression $target): bool
    {
        if (! $this->belongsToCurrentOrganisation($target) || $this->targetOutranksActor($actor, $target)) {
            return false;
        }

        return $this->roleChecker->isAdmin($actor)
            && $actor->can('delete email suppression')
            && $this->activeChecker->canBeDeleted($target);
    }

    /**
     * Determine whether the user can restore the email suppression.
     */
    public function canRestore(User $actor, EmailSuppression $target): bool
    {
        if (! $this->belongsToCurrentOrganisation($target) || $this->targetOutranksActor($actor, $target)) {
            return false;
        }

        return $actor->can('restore email suppression')
            && $this->activeChecker->canBeRestoredOrForceDeleted($target);
    }

    /**
     * Determine whether the user can permanently delete the email suppression.
     */
    public function canForceDelete(User $actor, EmailSuppression $target): bool
    {
        if (! $this->belongsToCurrentOrganisation($target) || $this->targetOutranksActor($actor, $target)) {
            return false;
        }

        return $this->activeChecker->canUserPerformAction(
            $actor,
            'restoreOrForceDelete',
            $target
        );
    }

    /**
     * Determine whether the user can import email suppressions.
     */
    public function canImport(User $actor): bool
    {
        return $actor->can('import email suppression');
    }

    /**
     * Determine whether the user can export email suppressions.
     */
    public function canExport(User $actor): bool
    {
        return $actor->can('export email suppression');
    }

    /**
     * Determine whether the email suppression was created by a user who outranks the actor.
     *
     * Prevents admins from managing entries created by super admins.
     */
    private function targetOutranksActor(
        User $actor,
        EmailSuppression $target
    ): bool {
        if ($this->roleChecker->isSuperAdmin($actor)) {
            return false;
        }

        $creator = $target->loadMissing('creator')->creator;

        if (! $creator instanceof User) {
            return false;
        }

        return $this->roleChecker->isSuperAdmin($creator);
    }
}
