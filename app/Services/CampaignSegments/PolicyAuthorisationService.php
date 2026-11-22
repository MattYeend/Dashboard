<?php

declare(strict_types=1);

namespace App\Services\CampaignSegments;

use App\Models\CampaignSegment;
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
     * Check if the campaign segment is active (not soft-deleted).
     */
    public function isActive(CampaignSegment $campaignSegment): bool
    {
        return $this->activeChecker->isActive($campaignSegment);
    }

    /**
     * Check if the campaign segment is soft-deleted.
     */
    public function isTrashed(CampaignSegment $campaignSegment): bool
    {
        return $this->activeChecker->isTrashed($campaignSegment);
    }

    /**
     * Determine whether the user can view any campaign segments.
     */
    public function canViewAny(User $actor): bool
    {
        return $actor->can('view any campaign segment');
    }

    /**
     * Determine whether the user can create campaign segments.
     */
    public function canCreate(User $actor): bool
    {
        return $actor->can('create campaign segment');
    }

    /**
     * Determine whether the user can view the campaign segment.
     */
    public function canView(User $actor, CampaignSegment $target): bool
    {
        if ($this->isBlocked($actor, $target)) {
            return false;
        }

        return $actor->can('view campaign segment')
            && $this->activeChecker->isActive($target);
    }

    /**
     * Determine whether the user can update the campaign segment.
     */
    public function canUpdate(User $actor, CampaignSegment $target): bool
    {
        if ($this->isBlocked($actor, $target)) {
            return false;
        }

        return $actor->can('edit campaign segment')
            && $this->activeChecker->isActive($target);
    }

    /**
     * Determine whether the user can delete the campaign segment.
     */
    public function canDelete(User $actor, CampaignSegment $target): bool
    {
        if ($this->isBlocked($actor, $target)) {
            return false;
        }

        return $actor->can('delete campaign segment')
            && $this->activeChecker->canBeModified($target);
    }

    /**
     * Determine whether the user can restore the campaign segment.
     */
    public function canRestore(User $actor, CampaignSegment $target): bool
    {
        if ($this->isBlocked($actor, $target)) {
            return false;
        }

        return $actor->can('restore campaign segment')
            && $this->activeChecker->canBeRestoredOrForceDeleted($target);
    }

    /**
     * Determine whether the user can permanently delete the campaign segment.
     */
    public function canForceDelete(User $actor, CampaignSegment $target): bool
    {
        if ($this->isBlocked($actor, $target)) {
            return false;
        }

        return $this->activeChecker->canUserPerformAction(
            $actor,
            'restoreOrForceDelete',
            $target
        );
    }

    /**
     * Determine whether the user can import campaign segments.
     */
    public function canImport(User $actor): bool
    {
        return $actor->can('import campaign segment');
    }

    /**
     * Determine whether the user can export campaign segments.
     */
    public function canExport(User $actor): bool
    {
        return $actor->can('export campaign segment');
    }

    /**
     * Determine whether the actor is blocked from the target by
     * organisation boundary or role rank.
     */
    private function isBlocked(User $actor, CampaignSegment $target): bool
    {
        return ! $this->belongsToCurrentOrganisation($target)
            || $this->targetOutranksActor($actor, $target);
    }

    /**
     * Determine whether the segment was created by a user who outranks the actor.
     *
     * Prevents admins from managing segments created by super admins.
     */
    private function targetOutranksActor(User $actor, CampaignSegment $target): bool
    {
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