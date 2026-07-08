<?php

namespace App\Policies;

use App\Models\Plan;
use App\Models\User;
use App\Services\Plans\PolicyAuthorisationService;

class PlanPolicy
{
    /**
     * Inject the required services into the policy.
     */
    public function __construct(
        protected PolicyAuthorisationService $authorisationService
    ) {}

    /**
     * Determine whether the user can view any tasks.
     */
    public function viewAny(User $user): bool
    {
        return $this->authorisationService->isAdmin($user);
    }

    /**
     * Determine whether the user can view the plan.
     */
    public function view(User $user, Plan $plan): bool
    {
        return $this->authorisationService->canView($user, $plan);
    }

    /**
     * Determine whether the user can create tasks.
     */
    public function create(User $user): bool
    {
        return $this->authorisationService->isAdmin($user);
    }

    /**
     * Determine whether the user can update the plan.
     */
    public function update(User $user, Plan $plan): bool
    {
        return $this->authorisationService->canUpdate($user, $plan);
    }

    /**
     * Determine whether the user can delete the plan.
     */
    public function delete(User $user, Plan $plan): bool
    {
        return $this->authorisationService->canDelete($user, $plan);
    }

    /**
     * Determine whether the user can restore the plan.
     */
    public function restore(User $user, Plan $plan): bool
    {
        return $this->authorisationService->canRestore($user, $plan);
    }

    /**
     * Determine whether the user can permanently delete the plan.
     */
    public function forceDelete(User $user, Plan $plan): bool
    {
        return $this->authorisationService->canForceDelete($user, $plan);
    }
}
