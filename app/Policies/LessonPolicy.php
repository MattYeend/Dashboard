<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Course;
use App\Models\Lesson;
use App\Models\User;
use App\Services\Lessons\PolicyAuthorisationService;

/**
 * Authorisation rules for lessons.
 */
class LessonPolicy
{
    /**
     * Create a new policy instance.
     */
    public function __construct(private readonly PolicyAuthorisationService $authorisationService) {}

    /**
     * Determine whether the user can view the lesson list.
     */
    public function viewAny(User $user): bool
    {
        return $this->authorisationService->canViewAny($user);
    }

    /**
     * Determine whether the user can view the lesson.
     */
    public function view(User $user, Lesson $lesson): bool
    {
        return $this->authorisationService->canView($user, $lesson);
    }

    /**
     * Determine whether the user can create lessons.
     */
    public function create(User $user): bool
    {
        return $this->authorisationService->canCreate($user);
    }

    /**
     * Determine whether the user can update the lesson.
     */
    public function update(User $user, Lesson $lesson): bool
    {
        return $this->authorisationService->canUpdate($user, $lesson);
    }

    /**
     * Determine whether the user can reorder the lessons of a course.
     */
    public function reorder(User $user, Course $course): bool
    {
        return $this->authorisationService->canReorder($user, $course);
    }

    /**
     * Determine whether the user can delete the lesson.
     */
    public function delete(User $user, Lesson $lesson): bool
    {
        return $this->authorisationService->canDelete($user, $lesson);
    }

    /**
     * Determine whether the user can restore the lesson.
     */
    public function restore(User $user, Lesson $lesson): bool
    {
        return $this->authorisationService->canRestore($user, $lesson);
    }

    /**
     * Determine whether the user can permanently delete the lesson.
     */
    public function forceDelete(User $user, Lesson $lesson): bool
    {
        return $this->authorisationService->canForceDelete($user, $lesson);
    }

    /**
     * Determine whether the user can import lessons.
     */
    public function import(User $user): bool
    {
        return $this->authorisationService->canImport($user);
    }

    /**
     * Determine whether the user can export lessons.
     */
    public function export(User $user): bool
    {
        return $this->authorisationService->canExport($user);
    }

    /**
     * Determine whether the user can bulk delete lessons.
     */
    public function bulkDelete(User $user): bool
    {
        return $this->authorisationService->isAdmin($user);
    }

    /**
     * Determine whether the user can bulk restore lessons.
     */
    public function bulkRestore(User $user): bool
    {
        return $this->authorisationService->isAdmin($user);
    }
}
