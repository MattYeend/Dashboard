<?php

declare(strict_types=1);

namespace App\Services\Answers;

use App\Models\Answer;
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
     * Determine whether the user can view any answers.
     */
    public function canViewAny(User $actor): bool
    {
        return $actor->can('view any answer');
    }

    /**
     * Determine whether the user can create answers.
     */
    public function canCreate(User $actor): bool
    {
        return $actor->can('create answer');
    }

    /**
     * Determine whether the user can view the answer.
     */
    public function canView(User $actor, Answer $target): bool
    {
        if (! $this->belongsToCurrentOrganisation($target) || $this->targetOutranksActor($actor, $target)) {
            return false;
        }

        return $actor->can('view answer')
            && $this->activeChecker->isActive($target);
    }

    /**
     * Determine whether the user can update the answer.
     */
    public function canUpdate(User $actor, Answer $target): bool
    {
        if (! $this->belongsToCurrentOrganisation($target) || $this->targetOutranksActor($actor, $target)) {
            return false;
        }

        return $actor->can('edit answer')
            && $this->activeChecker->isActive($target);
    }

    /**
     * Determine whether the user can delete the answer.
     */
    public function canDelete(User $actor, Answer $target): bool
    {
        if (! $this->belongsToCurrentOrganisation($target) || $this->targetOutranksActor($actor, $target)) {
            return false;
        }

        return $actor->can('delete answer')
            && $this->activeChecker->canBeModified($target);
    }

    /**
     * Determine whether the user can restore the answer.
     */
    public function canRestore(User $actor, Answer $target): bool
    {
        if (! $this->belongsToCurrentOrganisation($target) || $this->targetOutranksActor($actor, $target)) {
            return false;
        }

        return $actor->can('restore answer')
            && $this->activeChecker->canBeRestoredOrForceDeleted($target);
    }

    /**
     * Determine whether the user can permanently delete the answer.
     */
    public function canForceDelete(User $actor, Answer $target): bool
    {
        if (! $this->belongsToCurrentOrganisation($target) || $this->targetOutranksActor($actor, $target)) {
            return false;
        }

        return $actor->can('force delete answer')
            && $this->activeChecker->canUserPerformAction($actor, 'restoreOrForceDelete', $target);
    }

    /**
     * Determine whether the user can bulk delete answers.
     */
    public function canBulkDelete(User $actor): bool
    {
        return $actor->can('delete answer');
    }

    /**
     * Determine whether the user can bulk restore answers.
     */
    public function canBulkRestore(User $actor): bool
    {
        return $actor->can('restore answer');
    }

    /**
     * Determine whether the user can import answers.
     */
    public function canImport(User $actor): bool
    {
        return $actor->can('import answer');
    }

    /**
     * Determine whether the user can export answers.
     */
    public function canExport(User $actor): bool
    {
        return $actor->can('export answer');
    }

    /**
     * Determine whether the user can see which answers are correct.
     */
    public function canViewCorrect(User $actor): bool
    {
        return $actor->can('view correct answer');
    }

    /**
     * Determine whether the answer was created by a user who outranks the actor.
     *
     * Prevents admins from managing answers created by super admins.
     */
    private function targetOutranksActor(User $actor, Answer $target): bool
    {
        if ($this->roleChecker->isSuperAdmin($actor)) {
            return false;
        }

        $creator = $target->loadMissing('creator')->creator;

        if (! $creator instanceof User) {
            return false;
        }

        return $this->roleChecker->isSuperAdmin($creator);
    }
}
