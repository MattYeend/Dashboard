<?php

namespace App\Policies;

use App\Models\Pipeline;
use App\Models\User;
use App\Services\Pipelines\PolicyAuthorisationService;

class PipelinePolicy
{
    /**
     * Inject the required services into the policy.
     */
    public function __construct(
        protected PolicyAuthorisationService $authorisationService
    ) {}

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
    public function view(User $user, Pipeline $pipeline): bool
    {
        return $this->authorisationService->canView($user, $pipeline);
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
    public function update(User $user, Pipeline $pipeline): bool
    {
        return $this->authorisationService->canUpdate($user, $pipeline);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Pipeline $pipeline): bool
    {
        return $this->authorisationService->canDelete($user, $pipeline);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Pipeline $pipeline): bool
    {
        return $this->authorisationService->canRestore($user, $pipeline);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Pipeline $pipeline): bool
    {
        return $this->authorisationService->canForceDelete($user, $pipeline);
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
     * Determine whether the user can import models.
     */
    public function import(User $user): bool
    {
        return $this->authorisationService->canImport($user);
    }

    /**
     * Determine whether the user can export models.
     */
    public function export(User $user): bool
    {
        return $this->authorisationService->canExport($user);
    }

    /**
     * Determine whether the user can assign the pipeline.
     */
    public function assign(User $user, Pipeline $pipeline): bool
    {
        return $this->authorisationService->canAssign($user, $pipeline);
    }
}
