<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Quiz;
use App\Models\User;
use App\Services\Quizzes\PolicyAuthorisationService;

/**
 * Authorisation rules for quizzes.
 */
class QuizPolicy
{
    /**
     * Create a new policy instance.
     */
    public function __construct(private readonly PolicyAuthorisationService $authorisationService) {}

    /**
     * Determine whether the user can view the quiz list.
     */
    public function viewAny(User $user): bool
    {
        return $this->authorisationService->canViewAny($user);
    }

    /**
     * Determine whether the user can view the quiz.
     *
     * Non-editors are refused when the quiz or its course is unpublished.
     */
    public function view(User $user, Quiz $quiz): bool
    {
        return $this->authorisationService->canView($user, $quiz);
    }

    /**
     * Determine whether the user can create quizzes.
     */
    public function create(User $user): bool
    {
        return $this->authorisationService->canCreate($user);
    }

    /**
     * Determine whether the user can update the quiz.
     */
    public function update(User $user, Quiz $quiz): bool
    {
        return $this->authorisationService->canUpdate($user, $quiz);
    }

    /**
     * Determine whether the user can delete the quiz.
     */
    public function delete(User $user, Quiz $quiz): bool
    {
        return $this->authorisationService->canDelete($user, $quiz);
    }

    /**
     * Determine whether the user can restore the quiz.
     */
    public function restore(User $user, Quiz $quiz): bool
    {
        return $this->authorisationService->canRestore($user, $quiz);
    }

    /**
     * Determine whether the user can permanently delete the quiz.
     */
    public function forceDelete(User $user, Quiz $quiz): bool
    {
        return $this->authorisationService->canForceDelete($user, $quiz);
    }

    /**
     * Determine whether the user can import quizzes.
     */
    public function import(User $user): bool
    {
        return $this->authorisationService->canImport($user);
    }

    /**
     * Determine whether the user can export quizzes.
     */
    public function export(User $user): bool
    {
        return $this->authorisationService->canExport($user);
    }

    /**
     * Determine whether the user can bulk delete quizzes.
     */
    public function bulkDelete(User $user): bool
    {
        return $this->authorisationService->isAdmin($user);
    }

    /**
     * Determine whether the user can bulk restore quizzes.
     */
    public function bulkRestore(User $user): bool
    {
        return $this->authorisationService->isAdmin($user);
    }
}
