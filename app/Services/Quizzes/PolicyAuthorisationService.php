<?php

declare(strict_types=1);

namespace App\Services\Quizzes;

use App\Models\Quiz;
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
     * Check if quiz is active (not soft-deleted).
     */
    public function isActive(Quiz $quiz): bool
    {
        return $this->activeChecker->isActive($quiz);
    }

    /**
     * Check if quiz is soft-deleted.
     */
    public function isTrashed(Quiz $quiz): bool
    {
        return $this->activeChecker->isTrashed($quiz);
    }

    /**
     * Determine whether the user can view any quizzes.
     */
    public function canViewAny(User $actor): bool
    {
        return $actor->can('view any quizzes');
    }

    /**
     * Determine whether the user can create quizzes.
     */
    public function canCreate(User $actor): bool
    {
        return $actor->can('create quizzes');
    }

    /**
     * Determine whether the user can view the quiz. Non-editors only see
     * published quizzes in published courses.
     */
    public function canView(User $actor, Quiz $target): bool
    {
        if (! $this->belongsToCurrentOrganisation($target)) {
            return false;
        }

        if (! $actor->can('view quizzes') || ! $this->activeChecker->isActive($target)) {
            return false;
        }

        if ($actor->can('edit quizzes')) {
            return true;
        }

        $target->loadMissing('course');

        return $target->isVisibleToLearners();
    }

    /**
     * Determine whether the user can update the quiz.
     */
    public function canUpdate(User $actor, Quiz $target): bool
    {
        if (! $this->belongsToCurrentOrganisation($target)) {
            return false;
        }

        return $actor->can('edit quizzes')
            && $this->activeChecker->isActive($target);
    }

    /**
     * Determine whether the user can delete the quiz.
     */
    public function canDelete(User $actor, Quiz $target): bool
    {
        if (! $this->belongsToCurrentOrganisation($target)) {
            return false;
        }

        return $actor->can('delete quizzes')
            && $this->activeChecker->canBeModified($target);
    }

    /**
     * Determine whether the user can restore the quiz.
     */
    public function canRestore(User $actor, Quiz $target): bool
    {
        if (! $this->belongsToCurrentOrganisation($target)) {
            return false;
        }

        return $actor->can('restore quizzes')
            && $this->activeChecker->canBeRestoredOrForceDeleted($target);
    }

    /**
     * Determine whether the user can permanently delete the quiz.
     */
    public function canForceDelete(User $actor, Quiz $target): bool
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
     * Determine whether the user can import quizzes.
     */
    public function canImport(User $actor): bool
    {
        return $actor->can('import quizzes');
    }

    /**
     * Determine whether the user can export quizzes.
     */
    public function canExport(User $actor): bool
    {
        return $actor->can('export quizzes');
    }
}
