<?php

namespace App\Policies;

use App\Models\NotificationBroadcast;
use App\Models\User;
use App\Services\NotificationBroadcasts\PolicyAuthorisationService;

class NotificationBroadcastPolicy
{
    /**
     * The authorisation service handling permission checks.
     */
    protected PolicyAuthorisationService $authorisationService;

    /**
     * Inject the required service into the policy.
     */
    public function __construct(
        PolicyAuthorisationService $authorisationService
    ) {
        $this->authorisationService = $authorisationService;
    }

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $this->authorisationService->canViewAny($user);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, NotificationBroadcast $notificationBroadcast): bool
    {
        return $this->authorisationService->canView($user, $notificationBroadcast);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $this->authorisationService->canCreate($user);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, NotificationBroadcast $notificationBroadcast): bool
    {
        return $this->authorisationService->canUpdate($user, $notificationBroadcast);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, NotificationBroadcast $notificationBroadcast): bool
    {
        return $this->authorisationService->canDelete($user, $notificationBroadcast);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, NotificationBroadcast $notificationBroadcast): bool
    {
        return $this->authorisationService->isAdmin($user);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, NotificationBroadcast $notificationBroadcast): bool
    {
        return $this->authorisationService->isAdmin($user);
    }

    /**
     * Determine whether the user can bulk delete models.
     */
    public function bulkDelete(User $user): bool
    {
        return $this->authorisationService->isAdmin($user);
    }

    /**
     * Determine whether the user can bulk restore models.
     */
    public function bulkRestore(User $user): bool
    {
        return $this->authorisationService->isAdmin($user);
    }

    /**
     * Determine whether the user can send the model.
     */
    public function send(User $user, NotificationBroadcast $notificationBroadcast): bool
    {
        return $this->authorisationService->canSend($user, $notificationBroadcast);
    }
}
