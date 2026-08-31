<?php

namespace App\Policies;

use App\Models\User;
use App\Services\Settings\PolicyAuthorisationService;

class SettingPolicy
{
    /**
     * Inject the required services into the policy.
     */
    public function __construct(
        protected PolicyAuthorisationService $authorisationService
    ) {}

    /**
     * Determine whether the user can view the settings landing page.
     *
     * True if the user can view at least one of the three settings
     * groups — the page itself uses the `permissions` prop to decide
     * which group links to show.
     */
    public function viewAny(User $user): bool
    {
        return $this->authorisationService->canViewGeneral($user)
            || $this->authorisationService->canViewSystem($user)
            || $this->authorisationService->canViewSecurity($user);
    }

    /**
     * Determine whether the user can view the general settings group.
     */
    public function viewGeneral(User $user): bool
    {
        return $this->authorisationService->canViewGeneral($user);
    }

    /**
     * Determine whether the user can update the general settings group.
     */
    public function updateGeneral(User $user): bool
    {
        return $this->authorisationService->canUpdateGeneral($user);
    }

    /**
     * Determine whether the user can view the system settings group.
     */
    public function viewSystem(User $user): bool
    {
        return $this->authorisationService->canViewSystem($user);
    }

    /**
     * Determine whether the user can update the system settings group.
     */
    public function updateSystem(User $user): bool
    {
        return $this->authorisationService->canUpdateSystem($user);
    }

    /**
     * Determine whether the user can view the security settings group.
     */
    public function viewSecurity(User $user): bool
    {
        return $this->authorisationService->canViewSecurity($user);
    }

    /**
     * Determine whether the user can update the security settings group.
     */
    public function updateSecurity(User $user): bool
    {
        return $this->authorisationService->canUpdateSecurity($user);
    }
}
