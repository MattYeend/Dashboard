<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\CampaignRecipient;
use App\Models\User;
use App\Services\CampaignRecipients\PolicyAuthorisationService;

class CampaignRecipientPolicy
{
    /**
     * Inject the required services into the policy.
     */
    public function __construct(
        protected PolicyAuthorisationService $authorisationService
    ) {}

    /**
     * Determine whether the user can view any campaign recipients.
     */
    public function viewAny(User $user): bool
    {
        return $this->authorisationService->canViewAny($user);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, CampaignRecipient $campaignRecipient): bool
    {
        return $this->authorisationService->canView($user, $campaignRecipient);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, CampaignRecipient $campaignRecipient): bool
    {
        return $this->authorisationService->canDelete($user, $campaignRecipient);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, CampaignRecipient $campaignRecipient): bool
    {
        return $this->authorisationService->canRestore($user, $campaignRecipient);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, CampaignRecipient $campaignRecipient): bool
    {
        return $this->authorisationService->canForceDelete($user, $campaignRecipient);
    }

    /**
     * Determine whether the user can bulk delete campaign recipients.
     */
    public function bulkDelete(User $user): bool
    {
        return $this->authorisationService->isAdmin($user);
    }

    /**
     * Determine whether the user can bulk restore campaign recipients.
     */
    public function bulkRestore(User $user): bool
    {
        return $this->authorisationService->isAdmin($user);
    }

    /**
     * Determine whether the user can export campaign recipients.
     */
    public function export(User $user): bool
    {
        return $this->authorisationService->canExport($user);
    }
}
