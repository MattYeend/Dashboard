<?php

declare(strict_types=1);

namespace App\Services\CampaignRecipients;

use App\Models\CampaignRecipient;
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
     * Check if campaign recipient is active (not soft-deleted).
     */
    public function isActive(CampaignRecipient $campaignRecipient): bool
    {
        return $this->activeChecker->isActive($campaignRecipient);
    }

    /**
     * Check if campaign recipient is soft-deleted.
     */
    public function isTrashed(CampaignRecipient $campaignRecipient): bool
    {
        return $this->activeChecker->isTrashed($campaignRecipient);
    }

    /**
     * Determine whether the user can view any campaign recipients.
     */
    public function canViewAny(User $actor): bool
    {
        return $actor->can('view any campaign recipient');
    }

    /**
     * Determine whether the user can view the campaign recipient.
     */
    public function canView(User $actor, CampaignRecipient $target): bool
    {
        if (! $this->belongsToCurrentOrganisation($target) || $this->targetOutranksActor($actor, $target)) {
            return false;
        }

        return $actor->can('view campaign recipient')
            && $this->activeChecker->isActive($target);
    }

    /**
     * Determine whether the user can delete the campaign recipient.
     */
    public function canDelete(User $actor, CampaignRecipient $target): bool
    {
        if (! $this->belongsToCurrentOrganisation($target) || $this->targetOutranksActor($actor, $target)) {
            return false;
        }

        return $actor->can('delete campaign recipient')
            && $this->activeChecker->canBeDeleted($target);
    }

    /**
     * Determine whether the user can restore the campaign recipient.
     */
    public function canRestore(User $actor, CampaignRecipient $target): bool
    {
        if (! $this->belongsToCurrentOrganisation($target) || $this->targetOutranksActor($actor, $target)) {
            return false;
        }

        return $actor->can('restore campaign recipient')
            && $this->activeChecker->canBeRestoredOrForceDeleted($target);
    }

    /**
     * Determine whether the user can permanently delete the campaign recipient.
     */
    public function canForceDelete(User $actor, CampaignRecipient $target): bool
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
     * Determine whether the user can export campaign recipients.
     */
    public function canExport(User $actor): bool
    {
        return $actor->can('export campaign recipient');
    }

    /**
     * Determine whether the campaign recipient was created by a user who
     * outranks the actor.
     *
     * Prevents admins from managing campaign recipients created by super admins.
     */
    private function targetOutranksActor(
        User $actor,
        CampaignRecipient $target
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
