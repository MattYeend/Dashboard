<?php

namespace App\Policies;

use App\Models\PipelineStatus;
use App\Models\User;
use App\Services\PipelineStatuses\PolicyAuthorisationService;

class PipelineStatusPolicy
{
    /**
     * Inject the required services into the policy.
     */
    public function __construct(
        protected readonly PolicyAuthorisationService $authorisation
    ) {}

    /**
     * Determine whether the user can view any pipeline statuses.
     */
    public function viewAny(User $user): bool
    {
        return $this->authorisation->canViewAny($user);
    }

    /**
     * Determine whether the user can view the pipeline status.
     */
    public function view(User $user, PipelineStatus $pipelineStatus): bool
    {
        return $this->authorisation->canView($user, $pipelineStatus);
    }

    /**
     * Determine whether the user can create pipeline statuses.
     */
    public function create(User $user): bool
    {
        return $this->authorisation->canCreate($user);
    }

    /**
     * Determine whether the user can update the pipeline status.
     */
    public function update(User $user, PipelineStatus $pipelineStatus): bool
    {
        return $this->authorisation->canUpdate($user, $pipelineStatus);
    }

    /**
     * Determine whether the user can delete the pipeline status.
     */
    public function delete(User $user, PipelineStatus $pipelineStatus): bool
    {
        return $this->authorisation->canDelete($user, $pipelineStatus);
    }

    /**
     * Determine whether the user can restore the pipeline status.
     */
    public function restore(User $user, PipelineStatus $pipelineStatus): bool
    {
        return $this->authorisation->canRestore($user, $pipelineStatus);
    }

    /**
     * Determine whether the user can permanently delete the pipeline status.
     */
    public function forceDelete(User $user, PipelineStatus $pipelineStatus): bool
    {
        return $this->authorisation->canForceDelete($user, $pipelineStatus);
    }

    /**
     * Determine whether the user can import pipeline statuses.
     */
    public function import(User $user): bool
    {
        return $this->authorisation->canImport($user);
    }

    /**
     * Determine whether the user can export pipeline statuses.
     */
    public function export(User $user): bool
    {
        return $this->authorisation->canExport($user);
    }
}
