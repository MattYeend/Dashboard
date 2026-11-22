<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\CampaignSegment;
use App\Models\User;
use App\Services\CampaignSegments\PolicyAuthorisationService;

/**
 * Authorisation rules for campaign segments.
 */
class CampaignSegmentPolicy
{
    /**
     * Create a new policy instance.
     */
    public function __construct(private readonly PolicyAuthorisationService $authorisationService) {}

    /**
     * Determine whether the user can view the campaign segment list.
     */
    public function viewAny(User $user): bool
    {
        return $this->authorisationService->canViewAny($user);
    }

    /**
     * Determine whether the user can view the campaign segment.
     */
    public function view(User $user, CampaignSegment $campaignSegment): bool
    {
        return $this->authorisationService->canView($user, $campaignSegment);
    }

    /**
     * Determine whether the user can create campaign segments.
     */
    public function create(User $user): bool
    {
        return $this->authorisationService->canCreate($user);
    }

    /**
     * Determine whether the user can update the campaign segment.
     */
    public function update(User $user, CampaignSegment $campaignSegment): bool
    {
        return $this->authorisationService->canUpdate($user, $campaignSegment);
    }

    /**
     * Determine whether the user can delete the campaign segment.
     */
    public function delete(User $user, CampaignSegment $campaignSegment): bool
    {
        return $this->authorisationService->canDelete($user, $campaignSegment);
    }

    /**
     * Determine whether the user can restore the campaign segment.
     */
    public function restore(User $user, CampaignSegment $campaignSegment): bool
    {
        return $this->authorisationService->canRestore($user, $campaignSegment);
    }

    /**
     * Determine whether the user can permanently delete the campaign segment.
     */
    public function forceDelete(User $user, CampaignSegment $campaignSegment): bool
    {
        return $this->authorisationService->canForceDelete($user, $campaignSegment);
    }

    /**
     * Determine whether the user can import campaign segments.
     */
    public function import(User $user): bool
    {
        return $this->authorisationService->canImport($user);
    }

    /**
     * Determine whether the user can export campaign segments.
     */
    public function export(User $user): bool
    {
        return $this->authorisationService->canExport($user);
    }

    /**
     * Determine whether the user can bulk delete campaign segments.
     */
    public function bulkDelete(User $user): bool
    {
        return $this->authorisationService->isAdmin($user);
    }

    /**
     * Determine whether the user can bulk restore campaign segments.
     */
    public function bulkRestore(User $user): bool
    {
        return $this->authorisationService->isAdmin($user);
    }
}