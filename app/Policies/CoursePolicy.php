<?php
 
declare(strict_types=1);
 
namespace App\Policies;
 
use App\Models\Course;
use App\Models\User;
use App\Services\Courses\PolicyAuthorisationService;
 
/**
 * Authorisation rules for courses.
 */
class CoursePolicy
{
    /**
     * Create a new policy instance.
     */
    public function __construct(private readonly PolicyAuthorisationService $authorisationService)
    {
    }
 
    /**
     * Determine whether the user can view the course list.
     */
    public function viewAny(User $user): bool
    {
        return $this->authorisationService->canViewAny($user);
    }
 
    /**
     * Determine whether the user can view the course.
     */
    public function view(User $user, Course $course): bool
    {
        return $this->authorisationService->canView($user, $course);
    }
 
    /**
     * Determine whether the user can create courses.
     */
    public function create(User $user): bool
    {
        return $this->authorisationService->canCreate($user);
    }
 
    /**
     * Determine whether the user can update the course.
     */
    public function update(User $user, Course $course): bool
    {
        return $this->authorisationService->canUpdate($user, $course);
    }
 
    /**
     * Determine whether the user can delete the course.
     */
    public function delete(User $user, Course $course): bool
    {
        return $this->authorisationService->canDelete($user, $course);
    }
 
    /**
     * Determine whether the user can restore the course.
     */
    public function restore(User $user, Course $course): bool
    {
        return $this->authorisationService->canRestore($user, $course);
    }
 
    /**
     * Determine whether the user can permanently delete the course.
     */
    public function forceDelete(User $user, Course $course): bool
    {
        return $this->authorisationService->canForceDelete($user, $course);
    }
 
    /**
     * Determine whether the user can import courses.
     */
    public function import(User $user): bool
    {
        return $this->authorisationService->canImport($user);
    }
 
    /**
     * Determine whether the user can export courses.
     */
    public function export(User $user): bool
    {
        return $this->authorisationService->canExport($user);
    }
 
    /**
     * Determine whether the user can bulk delete courses.
     */
    public function bulkDelete(User $user): bool
    {
        return $this->authorisationService->isAdmin($user);
    }
 
    /**
     * Determine whether the user can bulk restore courses.
     */
    public function bulkRestore(User $user): bool
    {
        return $this->authorisationService->isAdmin($user);
    }
}
