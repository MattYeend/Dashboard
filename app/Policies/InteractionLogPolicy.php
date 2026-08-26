<?php

namespace App\Policies;

use App\Models\InteractionLog;
use App\Models\User;
use App\Services\InteractionLogs\PolicyAuthorisationService;

class InteractionLogPolicy
{
    public function __construct(
        private readonly PolicyAuthorisationService $authorisationService,
    ) {}

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
    public function update(User $user, InteractionLog $interactionLog): bool
    {
        return $this->authorisationService->canUpdate($user, $interactionLog);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, InteractionLog $interactionLog): bool
    {
        return $this->authorisationService->canDelete($user, $interactionLog);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, InteractionLog $interactionLog): bool
    {
        return $this->authorisationService->canForceDelete($user, $interactionLog);
    }
}
