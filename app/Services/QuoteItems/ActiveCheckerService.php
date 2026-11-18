<?php

declare(strict_types=1);

namespace App\Services\QuoteItems;

use App\Models\QuoteItem;
use App\Models\User;
use App\Services\UserRoleCheckerService;

class ActiveCheckerService
{
    /**
     * Inject the required services into the active checker service.
     */
    public function __construct(
        protected readonly UserRoleCheckerService $roleChecker
    ) {}

    /**
     * Check if quoteItem is active (not soft-deleted).
     */
    public function isActive(
        QuoteItem $quoteItem
    ): bool {
        return ! $quoteItem->trashed();
    }

    /**
     * Check if quoteItem is soft-deleted.
     */
    public function isTrashed(
        QuoteItem $quoteItem
    ): bool {
        return $quoteItem->trashed();
    }

    /**
     * Check if quoteItem is active and can be updated or deleted.
     */
    public function canBeModified(
        QuoteItem $quoteItem
    ): bool {
        return $this->isActive(
            $quoteItem
        );
    }

    /**
     * Check if quoteItem is soft-deleted and can be restored or force-deleted.
     */
    public function canBeRestoredOrForceDeleted(
        QuoteItem $quoteItem
    ): bool {
        return $this->isTrashed(
            $quoteItem
        );
    }

    /**
     * Check if the user can perform an action on the quoteItem.
     */
    public function canUserPerformAction(
        User $actor,
        string $action,
        QuoteItem $target
    ): bool {
        return match ($action) {
            'modify' => $this->roleChecker->isAdmin($actor) && $this->canBeModified($target),
            'restoreOrForceDelete' => $this->roleChecker->isAdmin($actor) && $this->canBeRestoredOrForceDeleted($target),
            default => false,
        };
    }
}
