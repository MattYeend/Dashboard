<?php

namespace App\Policies;

use App\Models\PipelineStage;
use App\Models\User;
use App\Services\PipelineStages\PolicyAuthorisationService;

class PipelineStagePolicy
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
    public function view(User $user, PipelineStage $pipelineStage): bool
    {
        return $this->authorisationService->canView($user, $pipelineStage);
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
    public function update(User $user, PipelineStage $pipelineStage): bool
    {
        return $this->authorisationService->canUpdate($user, $pipelineStage);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, PipelineStage $pipelineStage): bool
    {
        return $this->authorisationService->canDelete($user, $pipelineStage);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, PipelineStage $pipelineStage): bool
    {
        return $this->authorisationService->canRestore($user, $pipelineStage);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, PipelineStage $pipelineStage): bool
    {
        return $this->authorisationService->canForceDelete($user, $pipelineStage);
    }

    /**
     * Determine whether the user can assign the pipeline stage.
     */
    public function assign(User $user, PipelineStage $pipelineStage): bool
    {
        return $this->authorisationService->canAssign($user, $pipelineStage);
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
}
