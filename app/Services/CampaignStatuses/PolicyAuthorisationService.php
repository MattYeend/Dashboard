<?php

declare(strict_types=1);

namespace App\Services\CampaignStatuses;

use App\Models\CampaignStatus;
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
     * Check if campaign status is active (not soft-deleted).
     */
    public function isActive(CampaignStatus $campaignStatus): bool
    {
        return $this->activeChecker->isActive($campaignStatus);
    }

    /**
     * Check if campaign status is soft-deleted.
     */
    public function isTrashed(CampaignStatus $campaignStatus): bool
    {
        return $this->activeChecker->isTrashed($campaignStatus);
    }

    /**
     * Determine whether the user can view any campaign statuses.
     */
    public function canViewAny(User $actor): bool
    {
        return $actor->can('view campaign statuses');
    }

    /**
     * Determine whether the user can create campaign statuses.
     */
    public function canCreate(User $actor): bool
    {
        return $actor->can('create campaign statuses');
    }

    /**
     * Determine whether the user can view the campaign status.
     */
    public function canView(User $actor, CampaignStatus $target): bool
    {
        if (! $this->belongsToCurrentOrganisation($target) || $this->targetOutranksActor($actor, $target)) {
            return false;
        }

        return $actor->can('view campaign statuses')
            && $this->activeChecker->isActive($target);
    }

    /**
     * Determine whether the user can update the campaign status.
     */
    public function canUpdate(User $actor, CampaignStatus $target): bool
    {
        if (! $this->belongsToCurrentOrganisation($target) || $this->targetOutranksActor($actor, $target)) {
            return false;
        }

        return $actor->can('edit campaign statuses')
            && $this->activeChecker->isActive($target);
    }

    /**
     * Determine whether the user can delete the campaign status.
     */
    public function canDelete(User $actor, CampaignStatus $target): bool
    {
        if (! $this->belongsToCurrentOrganisation($target) || $this->targetOutranksActor($actor, $target)) {
            return false;
        }

        return $actor->can('delete campaign statuses')
            && $this->activeChecker->canBeModified($target);
    }

    /**
     * Determine whether the user can restore the campaign status.
     */
    public function canRestore(User $actor, CampaignStatus $target): bool
    {
        if (! $this->belongsToCurrentOrganisation($target) || $this->targetOutranksActor($actor, $target)) {
            return false;
        }

        return $actor->can('restore campaign statuses')
            && $this->activeChecker->canBeRestoredOrForceDeleted($target);
    }

    /**
     * Determine whether the user can permanently delete the campaign status.
     */
    public function canForceDelete(User $actor, CampaignStatus $target): bool
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
     * Determine whether the user can import campaign statuses.
     */
    public function canImport(User $actor): bool
    {
        return $actor->can('import campaign statuses');
    }

    /**
     * Determine whether the user can export campaign statuses.
     */
    public function canExport(User $actor): bool
    {
        return $actor->can('export campaign statuses');
    }

    /**
     * Determine whether the user can assign the campaign status.
     */
    public function canAssign(User $actor, CampaignStatus $target): bool
    {
        if (! $this->belongsToCurrentOrganisation($target) || $this->targetOutranksActor($actor, $target)) {
            return false;
        }

        return $actor->can('assign campaign statuses')
            && $this->activeChecker->isActive($target);
    }

    /**
     * Determine whether the campaign status was created by a user who outranks the actor.
     *
     * Prevents admins from managing campaign statuses created by super admins.
     */
    private function targetOutranksActor(
        User $actor,
        CampaignStatus $target
    ): bool {
        if ($this->roleChecker->isSuperAdmin($actor)) {
            return false;
        }

        $creator = $target->loadmissing('creator')->creator;

        if (! $creator instanceof User) {
            return false;
        }

        return $this->roleChecker->isSuperAdmin($creator);
    }
}
