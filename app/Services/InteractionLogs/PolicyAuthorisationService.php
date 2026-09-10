<?php

namespace App\Services\InteractionLogs;

use App\Models\InteractionLog;
use App\Models\User;
use App\Services\Concerns\ChecksOrganisationBoundary;
use App\Services\UserRoleCheckerService;

class PolicyAuthorisationService
{
    use ChecksOrganisationBoundary;

    public function __construct(
        private readonly UserRoleCheckerService $userRoleCheckerService,
    ) {}

    /**
     * Determine whether the actor holds the standard user role.
     */
    public function isUser(User $user): bool
    {
        return $this->userRoleCheckerService->isUser($user);
    }

    /**
     * Determine whether the actor is an admin.
     */
    public function isAdmin(User $user): bool
    {
        return $this->userRoleCheckerService->isAdmin($user);
    }

    /**
     * Determine whether the actor can create an interaction log.
     */
    public function canCreate(User $user): bool
    {
        return $user->can('create interaction logs');
    }

    /**
     * Determine whether the actor can update the given interaction log.
     */
    public function canUpdate(User $user, InteractionLog $target): bool
    {
        if (! $this->belongsToCurrentOrganisation($target) || $this->targetOutranksActor($user, $target)) {
            return false;
        }

        return $user->can('update interaction logs');
    }

    /**
     * Determine whether the actor can delete the given interaction log.
     */
    public function canDelete(User $user, InteractionLog $target): bool
    {
        if (! $this->belongsToCurrentOrganisation($target) || $this->targetOutranksActor($user, $target)) {
            return false;
        }

        return $user->can('delete interaction logs');
    }

    /**
     * Determine whether the actor can permanently delete the given interaction log.
     */
    public function canForceDelete(User $user, InteractionLog $target): bool
    {
        if (! $this->belongsToCurrentOrganisation($target) || $this->targetOutranksActor($user, $target)) {
            return false;
        }

        return $user->can('force delete interaction logs');
    }

    /**
     * Block the action if the log's creator is a Super Admin and the actor isn't.
     */
    private function targetOutranksActor(User $actor, InteractionLog $target): bool
    {
        $creator = $target->creator;

        return $creator instanceof User
            && $this->userRoleCheckerService->isSuperAdmin($creator)
            && ! $this->userRoleCheckerService->isSuperAdmin($actor);
    }
}
