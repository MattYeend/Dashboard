<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Campaign;
use App\Models\User;
use App\Services\Campaigns\PolicyAuthorisationService;

class CampaignPolicy
{
    /**
     * Inject the required services into the policy.
     */
    public function __construct(
        protected PolicyAuthorisationService $authorisationService
    ) {}

    /**
     * Determine whether the user can view any campaigns.
     */
    public function viewAny(User $user): bool
    {
        return $this->authorisationService->canViewAny($user);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Campaign $campaign): bool
    {
        return $this->authorisationService->canView($user, $campaign);
    }

    /**
     * Determine whether the user can create campaigns.
     */
    public function create(User $user): bool
    {
        return $this->authorisationService->canCreate($user);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Campaign $campaign): bool
    {
        return $this->authorisationService->canUpdate($user, $campaign);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Campaign $campaign): bool
    {
        return $this->authorisationService->canDelete($user, $campaign);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Campaign $campaign): bool
    {
        return $this->authorisationService->canRestore($user, $campaign);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Campaign $campaign): bool
    {
        return $this->authorisationService->canForceDelete($user, $campaign);
    }

    /**
     * Determine whether the user can bulk delete campaigns.
     */
    public function bulkDelete(User $user): bool
    {
        return $this->authorisationService->isAdmin($user);
    }

    /**
     * Determine whether the user can bulk restore campaigns.
     */
    public function bulkRestore(User $user): bool
    {
        return $this->authorisationService->isAdmin($user);
    }

    /**
     * Determine whether the user can import campaigns.
     */
    public function import(User $user): bool
    {
        return $this->authorisationService->canImport($user);
    }

    /**
     * Determine whether the user can export campaigns.
     */
    public function export(User $user): bool
    {
        return $this->authorisationService->canExport($user);
    }

    /**
     * Determine whether the user can schedule the campaign.
     */
    public function schedule(User $user, Campaign $campaign): bool
    {
        return $this->authorisationService->canSchedule($user, $campaign);
    }

    /**
     * Determine whether the user can pause the campaign.
     */
    public function pause(User $user, Campaign $campaign): bool
    {
        return $this->authorisationService->canPause($user, $campaign);
    }

    /**
     * Determine whether the user can resume the campaign.
     *
     * Resuming shares the pause permission: whoever can pause can resume.
     */
    public function resume(User $user, Campaign $campaign): bool
    {
        return $this->authorisationService->canPause($user, $campaign);
    }

    /**
     * Determine whether the user can cancel the campaign.
     */
    public function cancel(User $user, Campaign $campaign): bool
    {
        return $this->authorisationService->canCancel($user, $campaign);
    }
}
