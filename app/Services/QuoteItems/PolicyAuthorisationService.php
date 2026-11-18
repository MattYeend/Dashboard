<?php

declare(strict_types=1);

namespace App\Services\QuoteItems;

use App\Models\Quote;
use App\Models\QuoteItem;
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
     * Check if quote item is active (not soft-deleted).
     */
    public function isActive(QuoteItem $quoteItem): bool
    {
        return $this->activeChecker->isActive($quoteItem);
    }

    /**
     * Check if quote item is soft-deleted.
     */
    public function isTrashed(QuoteItem $quoteItem): bool
    {
        return $this->activeChecker->isTrashed($quoteItem);
    }

    /**
     * Determine whether the user can view any quote items.
     */
    public function canViewAny(User $actor): bool
    {
        return $actor->can('view any quote item');
    }

    /**
     * Determine whether the user can create quote items.
     */
    public function canCreate(User $actor): bool
    {
        return $actor->can('create quote item');
    }

    /**
     * Determine whether the user can view the quote item.
     */
    public function canView(User $actor, QuoteItem $target): bool
    {
        if (! $this->belongsToCurrentOrganisation($target) || $this->targetOutranksActor($actor, $target)) {
            return false;
        }

        return $actor->can('view quote item') && $this->activeChecker->isActive($target);
    }

    /**
     * Determine whether the user can update the quote item.
     */
    public function canUpdate(User $actor, QuoteItem $target): bool
    {
        if (! $this->belongsToCurrentOrganisation($target) || $this->targetOutranksActor($actor, $target)) {
            return false;
        }

        return $actor->can('edit quote item') && $this->activeChecker->isActive($target);
    }

    /**
     * Determine whether the user can delete the quote item.
     */
    public function canDelete(User $actor, QuoteItem $target): bool
    {
        if (! $this->belongsToCurrentOrganisation($target) || $this->targetOutranksActor($actor, $target)) {
            return false;
        }

        return $actor->can('delete quote item') && $this->activeChecker->canBeModified($target);
    }

    /**
     * Determine whether the user can restore the quote item.
     */
    public function canRestore(User $actor, QuoteItem $target): bool
    {
        if (! $this->belongsToCurrentOrganisation($target) || $this->targetOutranksActor($actor, $target)) {
            return false;
        }

        return $actor->can('restore quote item') && $this->activeChecker->canBeRestoredOrForceDeleted($target);
    }

    /**
     * Determine whether the user can permanently delete the quote item.
     */
    public function canForceDelete(User $actor, QuoteItem $target): bool
    {
        if (! $this->belongsToCurrentOrganisation($target) || $this->targetOutranksActor($actor, $target)) {
            return false;
        }

        return $this->activeChecker->canUserPerformAction(
            $actor,
            'restoreOrForceDelete',
            $target
        );
    }

    /**
     * Determine whether the user can import quote items.
     */
    public function canImport(User $actor): bool
    {
        return $actor->can('import quote item');
    }

    /**
     * Determine whether the user can export quote items.
     */
    public function canExport(User $actor): bool
    {
        return $actor->can('export quote item');
    }

    /**
     * Determine whether the quote item's parent quote was created by a
     * user who outranks the actor.
     *
     * Prevents admins from managing quote items belonging to invoices
     * created by super admins.
     */
    private function targetOutranksActor(User $actor, QuoteItem $target): bool
    {
        if ($this->roleChecker->isSuperAdmin($actor)) {
            return false;
        }

        $target->loadMissing('quote.creator');

        $creator = $target->quote->creator;

        if (! $creator instanceof User) {
            return false;
        }

        return $this->roleChecker->isSuperAdmin($creator);
    }
}
