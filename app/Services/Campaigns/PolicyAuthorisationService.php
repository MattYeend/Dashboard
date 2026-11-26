<?php

declare(strict_types=1);

namespace App\Services\Campaigns;

use App\Models\Campaign;
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
     * Check if campaign is active (not soft-deleted).
     */
    public function isActive(Campaign $campaign): bool
    {
        return $this->activeChecker->isActive($campaign);
    }

    /**
     * Check if campaign is soft-deleted.
     */
    public function isTrashed(Campaign $campaign): bool
    {
        return $this->activeChecker->isTrashed($campaign);
    }

    /**
     * Determine whether the user can view any campaigns.
     */
    public function canViewAny(User $actor): bool
    {
        return $actor->can('view campaign');
    }

    /**
     * Determine whether the user can create campaigns.
     */
    public function canCreate(User $actor): bool
    {
        return $actor->can('create campaign');
    }

    /**
     * Determine whether the user can view the campaign.
     */
    public function canView(User $actor, Campaign $target): bool
    {
        if (! $this->belongsToCurrentOrganisation($target) || $this->targetOutranksActor($actor, $target)) {
            return false;
        }

        return $actor->can('view campaign')
            && $this->activeChecker->isActive($target);
    }

    /**
     * Determine whether the user can update the campaign.
     */
    public function canUpdate(User $actor, Campaign $target): bool
    {
        if (! $this->belongsToCurrentOrganisation($target) || $this->targetOutranksActor($actor, $target)) {
            return false;
        }

        return $actor->can('edit campaign')
            && $this->activeChecker->isEditable($target);
    }

    /**
     * Determine whether the user can delete the campaign.
     */
    public function canDelete(User $actor, Campaign $target): bool
    {
        if (! $this->belongsToCurrentOrganisation($target) || $this->targetOutranksActor($actor, $target)) {
            return false;
        }

        return $actor->can('delete campaign')
            && $this->activeChecker->canBeDeleted($target);
    }

    /**
     * Determine whether the user can restore the campaign.
     */
    public function canRestore(User $actor, Campaign $target): bool
    {
        if (! $this->belongsToCurrentOrganisation($target) || $this->targetOutranksActor($actor, $target)) {
            return false;
        }

        return $actor->can('restore campaign')
            && $this->activeChecker->canBeRestoredOrForceDeleted($target);
    }

    /**
     * Determine whether the user can permanently delete the campaign.
     */
    public function canForceDelete(User $actor, Campaign $target): bool
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
     * Determine whether the user can import campaigns.
     */
    public function canImport(User $actor): bool
    {
        return $actor->can('import campaign');
    }

    /**
     * Determine whether the user can export campaigns.
     */
    public function canExport(User $actor): bool
    {
        return $actor->can('export campaign');
    }

    /**
     * Determine whether the user can schedule the campaign.
     */
    public function canSchedule(User $actor, Campaign $target): bool
    {
        if (! $this->belongsToCurrentOrganisation($target) || $this->targetOutranksActor($actor, $target)) {
            return false;
        }

        return $actor->can('schedule campaign')
            && $this->activeChecker->isActive($target);
    }

    /**
     * Determine whether the user can pause or resume the campaign.
     */
    public function canPause(User $actor, Campaign $target): bool
    {
        if (! $this->belongsToCurrentOrganisation($target) || $this->targetOutranksActor($actor, $target)) {
            return false;
        }

        return $actor->can('pause campaign')
            && $this->activeChecker->isActive($target);
    }

    /**
     * Determine whether the user can cancel the campaign.
     */
    public function canCancel(User $actor, Campaign $target): bool
    {
        if (! $this->belongsToCurrentOrganisation($target) || $this->targetOutranksActor($actor, $target)) {
            return false;
        }

        return $actor->can('cancel campaign')
            && $this->activeChecker->isActive($target);
    }

    /**
     * Determine whether the campaign was created by a user who outranks the actor.
     *
     * Prevents admins from managing campaigns created by super admins.
     */
    private function targetOutranksActor(
        User $actor,
        Campaign $target
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
