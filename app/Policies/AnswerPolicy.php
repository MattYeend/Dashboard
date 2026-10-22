<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Answer;
use App\Models\User;
use App\Services\Answers\PolicyAuthorisationService;

class AnswerPolicy
{
    /**
     * Inject the required services into the policy.
     */
    public function __construct(
        protected readonly PolicyAuthorisationService $authorisation,
    ) {}

    /**
     * Determine whether the user can view any answers.
     */
    public function viewAny(User $user): bool
    {
        return $this->authorisation->canViewAny($user);
    }

    /**
     * Determine whether the user can view the answer.
     */
    public function view(User $user, Answer $answer): bool
    {
        return $this->authorisation->canView($user, $answer);
    }

    /**
     * Determine whether the user can create answers.
     */
    public function create(User $user): bool
    {
        return $this->authorisation->canCreate($user);
    }

    /**
     * Determine whether the user can update the answer.
     */
    public function update(User $user, Answer $answer): bool
    {
        return $this->authorisation->canUpdate($user, $answer);
    }

    /**
     * Determine whether the user can delete the answer.
     */
    public function delete(User $user, Answer $answer): bool
    {
        return $this->authorisation->canDelete($user, $answer);
    }

    /**
     * Determine whether the user can restore the answer.
     */
    public function restore(User $user, Answer $answer): bool
    {
        return $this->authorisation->canRestore($user, $answer);
    }

    /**
     * Determine whether the user can permanently delete the answer.
     */
    public function forceDelete(User $user, Answer $answer): bool
    {
        return $this->authorisation->canForceDelete($user, $answer);
    }

    /**
     * Determine whether the user can bulk delete answers.
     */
    public function bulkDelete(User $user): bool
    {
        return $this->authorisation->canBulkDelete($user);
    }

    /**
     * Determine whether the user can bulk restore answers.
     */
    public function bulkRestore(User $user): bool
    {
        return $this->authorisation->canBulkRestore($user);
    }

    /**
     * Determine whether the user can import answers.
     */
    public function import(User $user): bool
    {
        return $this->authorisation->canImport($user);
    }

    /**
     * Determine whether the user can export answers.
     */
    public function export(User $user): bool
    {
        return $this->authorisation->canExport($user);
    }

    /**
     * Determine whether the user can see which answers are correct.
     */
    public function viewCorrect(User $user): bool
    {
        return $this->authorisation->canViewCorrect($user);
    }
}
