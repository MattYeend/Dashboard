<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Question;
use App\Models\User;
use App\Services\Questions\PolicyAuthorisationService;

class QuestionPolicy
{
    /**
     * Inject the policy authorisation service.
     */
    public function __construct(
        protected readonly PolicyAuthorisationService $authorisation
    ) {}

    /**
     * Determine whether the user can view any questions.
     */
    public function viewAny(User $user): bool
    {
        return $this->authorisation->canViewAny($user);
    }

    /**
     * Determine whether the user can view the question.
     */
    public function view(User $user, Question $question): bool
    {
        return $this->authorisation->canView($user, $question);
    }

    /**
     * Determine whether the user can create questions.
     */
    public function create(User $user): bool
    {
        return $this->authorisation->canCreate($user);
    }

    /**
     * Determine whether the user can update the question.
     */
    public function update(User $user, Question $question): bool
    {
        return $this->authorisation->canUpdate($user, $question);
    }

    /**
     * Determine whether the user can delete the question.
     */
    public function delete(User $user, Question $question): bool
    {
        return $this->authorisation->canDelete($user, $question);
    }

    /**
     * Determine whether the user can restore the question.
     */
    public function restore(User $user, Question $question): bool
    {
        return $this->authorisation->canRestore($user, $question);
    }

    /**
     * Determine whether the user can permanently delete the question.
     */
    public function forceDelete(User $user, Question $question): bool
    {
        return $this->authorisation->canForceDelete($user, $question);
    }

    /**
     * Determine whether the user can import questions.
     */
    public function import(User $user): bool
    {
        return $this->authorisation->canImport($user);
    }

    /**
     * Determine whether the user can export questions.
     */
    public function export(User $user): bool
    {
        return $this->authorisation->canExport($user);
    }
}
