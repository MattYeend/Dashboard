<?php

namespace App\Policies;

use App\Models\Post;
use App\Models\User;
use App\Services\Posts\PolicyAuthorisationService;

class PostPolicy
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
    public function viewAny(
        User $user
    ): bool {
        return $this->authorisationService->canViewAny(
            $user
        );
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(
        User $user,
        Post $post
    ): bool {
        return $this->authorisationService->canView(
            $user,
            $post
        );
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(
        User $user
    ): bool {
        return $this->authorisationService->canCreate(
            $user
        );
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(
        User $user,
        Post $post
    ): bool {
        return $this->authorisationService->canUpdate(
            $user,
            $post
        );
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(
        User $user,
        Post $post
    ): bool {
        return $this->authorisationService->canDelete(
            $user,
            $post
        );
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(
        User $user,
        Post $post
    ): bool {
        return $this->authorisationService->canRestore(
            $user,
            $post
        );
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(
        User $user,
        Post $post
    ): bool {
        return $this->authorisationService->canForceDelete(
            $user,
            $post
        );
    }

    /**
     * Determine whether the user can import models.
     */
    public function import(
        User $user
    ): bool {
        return $this->authorisationService->canImport(
            $user
        );
    }

    /**
     * Determine whether the user can export models.
     */
    public function export(
        User $user
    ): bool {
        return $this->authorisationService->canExport(
            $user
        );
    }
}
