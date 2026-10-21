<?php

declare(strict_types=1);

namespace App\Services\Questions;

use App\Models\Question;
use App\Models\User;
use App\Services\Concerns\ChecksOrganisationBoundary;
use App\Services\UserRoleCheckerService;

class PolicyAuthorisationService
{
    use ChecksOrganisationBoundary;

    /**
     * Inject the required services into the policy authorisation service.
     */
    public function __construct(
        protected readonly ActiveCheckerService $activeChecker,
        protected readonly UserRoleCheckerService $roleChecker
    ) {}

    /**
     * Check if user is a regular user, admin, or super admin.
     */
    public function isUser(User $user): bool
    {
        return $this->roleChecker->isUser($user);
    }

    /**
     * Check if user is admin or super admin.
     */
    public function isAdmin(User $user): bool
    {
        return $this->roleChecker->isAdmin($user);
    }

    /**
     * Check if question is active (not soft-deleted).
     */
    public function isActive(Question $question): bool
    {
        return $this->activeChecker->isActive($question);
    }

    /**
     * Check if question is soft-deleted.
     */
    public function isTrashed(Question $question): bool
    {
        return $this->activeChecker->isTrashed($question);
    }

    /**
     * Determine whether the user can view any questions.
     */
    public function canViewAny(User $actor): bool
    {
        return $actor->can('view any question');
    }

    /**
     * Determine whether the user can create questions.
     */
    public function canCreate(User $actor): bool
    {
        return $actor->can('create question');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function canView(User $actor, Question $target): bool
    {
        if (! $this->belongsToCurrentOrganisation($target)) {
            return false;
        }

        return $actor->can('view question');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function canUpdate(User $actor, Question $target): bool
    {
        if (! $this->belongsToCurrentOrganisation($target)) {
            return false;
        }

        return $actor->can('edit question')
            && $this->activeChecker->isActive($target);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function canDelete(User $actor, Question $target): bool
    {
        if (! $this->belongsToCurrentOrganisation($target)) {
            return false;
        }

        return $actor->can('delete question')
            && $this->activeChecker->canBeModified($target);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function canRestore(
        User $actor,
        Question $target
    ): bool {
        if (! $this->belongsToCurrentOrganisation($target)) {
            return false;
        }

        return $actor->can('restore question')
            && $this->activeChecker->canBeRestoredOrForceDeleted($target);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function canForceDelete(User $actor, Question $target): bool
    {
        if (! $this->belongsToCurrentOrganisation($target)) {
            return false;
        }

        return $this->activeChecker->canUserPerformAction(
            $actor,
            'restoreOrForceDelete',
            $target
        );
    }

    /**
     * Determine whether the user can import questions.
     */
    public function canImport(User $actor): bool
    {
        return $actor->can('import question');
    }

    /**
     * Determine whether the user can export questions.
     */
    public function canExport(User $actor): bool
    {
        return $actor->can('export question');
    }
}
