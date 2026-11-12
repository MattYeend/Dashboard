<?php

declare(strict_types=1);

namespace App\Services\Quotes;

use App\Models\Quote;
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
     * Check if user is admin or super admin.
     */
    public function isAdmin(User $user): bool
    {
        return $this->roleChecker->isAdmin($user);
    }

    public function canViewAny(User $actor): bool
    {
        return $actor->can('view any quote');
    }

    public function canCreate(User $actor): bool
    {
        return $actor->can('create quote');
    }

    public function canView(User $actor, Quote $target): bool
    {
        return $this->withinBoundary($actor, $target)
            && $actor->can('view quote')
            && $this->activeChecker->isActive($target);
    }

    public function canUpdate(User $actor, Quote $target): bool
    {
        return $this->withinBoundary($actor, $target)
            && $actor->can('edit quote')
            && $this->activeChecker->isEditable($target);
    }

    public function canDelete(User $actor, Quote $target): bool
    {
        return $this->withinBoundary($actor, $target)
            && $actor->can('delete quote')
            && $this->activeChecker->canBeDeleted($target);
    }

    public function canRestore(User $actor, Quote $target): bool
    {
        return $this->withinBoundary($actor, $target)
            && $actor->can('restore quote')
            && $this->activeChecker->canBeRestoredOrForceDeleted($target);
    }

    public function canForceDelete(User $actor, Quote $target): bool
    {
        return $this->withinBoundary($actor, $target)
            && $this->activeChecker->canUserPerformAction($actor, 'restoreOrForceDelete', $target);
    }

    public function canSend(User $actor, Quote $target): bool
    {
        return $this->withinBoundary($actor, $target)
            && $actor->can('send quote')
            && $this->activeChecker->canBeSent($target);
    }

    public function canAccept(User $actor, Quote $target): bool
    {
        return $this->withinBoundary($actor, $target)
            && $actor->can('accept quote')
            && $this->activeChecker->canBeAnswered($target);
    }

    public function canDecline(User $actor, Quote $target): bool
    {
        return $this->withinBoundary($actor, $target)
            && $actor->can('decline quote')
            && $this->activeChecker->canBeAnswered($target);
    }

    /**
     * Converting creates an order, so the actor needs both permissions.
     */
    public function canConvert(User $actor, Quote $target): bool
    {
        return $this->withinBoundary($actor, $target)
            && $actor->can('convert quote')
            && $actor->can('create order')
            && $this->activeChecker->canBeConverted($target);
    }

    public function canImport(User $actor): bool
    {
        return $actor->can('import quote');
    }

    public function canExport(User $actor): bool
    {
        return $actor->can('export quote');
    }

    /**
     * Tenant boundary plus the Super Admin outranking rule.
     */
    private function withinBoundary(User $actor, Quote $target): bool
    {
        return $this->belongsToCurrentOrganisation($target)
            && ! $this->targetOutranksActor($actor, $target);
    }

    /**
     * A Super Admin customer cannot be managed by anyone but another Super Admin.
     */
    private function targetOutranksActor(User $actor, Quote $target): bool
    {
        if ($this->roleChecker->isSuperAdmin($actor)) {
            return false;
        }

        $target->loadMissing('quotable');

        $owner = $target->quotable;

        if (! $owner instanceof User) {
            return false;
        }

        return $this->roleChecker->isSuperAdmin($owner);
    }
}
