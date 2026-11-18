<?php

declare(strict_types=1);

namespace App\Services\QuoteItems;

use App\Models\Quote;
use App\Models\QuoteItem;
use App\Models\User;
use App\Services\Concerns\ChecksOrganisationBoundary;
use App\Services\Quotes\PolicyAuthorisationService as QuotePolicyService;
use App\Services\UserRoleCheckerService;

class PolicyAuthorisationService
{
    use ChecksOrganisationBoundary;

    /**
     * Inject the required services into the policy authorisation service.
     */
    public function __construct(
        protected readonly ActiveCheckerService $activeChecker,
        protected readonly UserRoleCheckerService $roleChecker,
        protected readonly QuotePolicyService $quotePolicy,
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
        return $actor->can('view any quote item');
    }

    /**
     * Adding a line needs the permission and the right to edit the quote.
     */
    public function canCreate(User $actor, Quote $quote): bool
    {
        return $actor->can('create quote item')
            && $this->quotePolicy->canUpdate($actor, $quote);
    }

    public function canView(User $actor, QuoteItem $target): bool
    {
        return $this->belongsToCurrentOrganisation($target)
            && $actor->can('view quote item')
            && $this->activeChecker->isActive($target);
    }

    public function canUpdate(User $actor, QuoteItem $target): bool
    {
        return $this->canModifyThrough($actor, $target)
            && $actor->can('edit quote item')
            && $this->activeChecker->canBeModified($target);
    }

    public function canDelete(User $actor, QuoteItem $target): bool
    {
        return $this->canModifyThrough($actor, $target)
            && $actor->can('delete quote item')
            && $this->activeChecker->canBeModified($target);
    }

    public function canRestore(User $actor, QuoteItem $target): bool
    {
        return $this->canModifyThrough($actor, $target)
            && $actor->can('restore quote item')
            && $this->activeChecker->canBeRestored($target);
    }

    public function canForceDelete(User $actor, QuoteItem $target): bool
    {
        return $this->belongsToCurrentOrganisation($target)
            && $this->activeChecker->canUserPerformAction($actor, 'restoreOrForceDelete', $target);
    }

    public function canImport(User $actor, Quote $quote): bool
    {
        return $actor->can('import quote item')
            && $this->quotePolicy->canUpdate($actor, $quote);
    }

    public function canExport(User $actor, Quote $quote): bool
    {
        return $actor->can('export quote item')
            && $this->quotePolicy->canView($actor, $quote);
    }

    /**
     * The parent quote must be editable by this user.
     */
    private function canModifyThrough(User $actor, QuoteItem $target): bool
    {
        if (! $this->belongsToCurrentOrganisation($target)) {
            return false;
        }

        $target->loadMissing('quote.status');

        return $this->quotePolicy->canUpdate($actor, $target->quote);
    }
}
