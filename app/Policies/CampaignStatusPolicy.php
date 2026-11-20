<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\CampaignStatus;
use App\Models\User;
use App\Services\CampaignStatuses\PolicyAuthorisationService;

class CampaignStatusPolicy
{
    /**
     * Inject the required services into the policy.
     */
    public function __construct(
        protected PolicyAuthorisationService $authorisationService
    ) {}

    /**
     * Determine whether the user can view any campaign statuses.
     */
    public function viewAny(User $user): bool
    {
        return $this->authorisationService->canViewAny($user);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, CampaignStatus $campaignStatus): bool
    {
        return $this->authorisationService->canView($user, $campaignStatus);
    }

    /**
     * Determine whether the user can create campaign statuses.
     */
    public function create(User $user): bool
    {
        return $this->authorisationService->canCreate($user);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, CampaignStatus $campaignStatus): bool
    {
        return $this->authorisationService->canUpdate($user, $campaignStatus);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, CampaignStatus $campaignStatus): bool
    {
        return $this->authorisationService->canDelete($user, $campaignStatus);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, CampaignStatus $campaignStatus): bool
    {
        return $this->authorisationService->canRestore($user, $campaignStatus);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, CampaignStatus $campaignStatus): bool
    {
        return $this->authorisationService->canForceDelete($user, $campaignStatus);
    }

    /**
     * Determine whether the user can import campaign statuses.
     */
    public function import(User $user): bool
    {
        return $this->authorisationService->canImport($user);
    }

    /**
     * Determine whether the user can export campaign statuses.
     */
    public function export(User $user): bool
    {
        return $this->authorisationService->canExport($user);
    }

    /**
     * Determine whether the user can assign the campaign status.
     */
    public function assign(User $user, CampaignStatus $campaignStatus): bool
    {
        return $this->authorisationService->canAssign($user, $campaignStatus);
    }
}
