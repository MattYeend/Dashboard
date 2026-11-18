<?php

declare(strict_types=1);

namespace App\Services\QuoteItems;

use App\Models\Quote;
use App\Models\QuoteItem;
use App\Models\User;
use App\Services\Quotes\ActiveCheckerService as QuoteActiveChecker;
use App\Services\UserRoleCheckerService;

class ActiveCheckerService
{
    /**
     * Inject the required services into the active checker service.
     */
    public function __construct(
        protected readonly UserRoleCheckerService $roleChecker,
        protected readonly QuoteActiveChecker $quoteChecker,
    ) {}

    /**
     * Check if the line is active (not soft-deleted).
     */
    public function isActive(QuoteItem $item): bool
    {
        return ! $item->trashed();
    }

    /**
     * Check if the line is soft-deleted.
     */
    public function isTrashed(QuoteItem $item): bool
    {
        return $item->trashed();
    }

    /**
     * Whether lines can currently be changed on the quote (Draft only).
     */
    public function quoteAcceptsChanges(Quote $quote): bool
    {
        return $this->quoteChecker->isEditable($quote);
    }

    /**
     * Check if the line is active and its quote is still a draft.
     */
    public function canBeModified(QuoteItem $item): bool
    {
        $item->loadMissing('quote');

        return $this->isActive($item) && $this->quoteAcceptsChanges($item->quote);
    }

    /**
     * Check if the line is soft-deleted and its quote is still a draft.
     */
    public function canBeRestored(QuoteItem $item): bool
    {
        $item->loadMissing('quote');

        return $this->isTrashed($item) && $this->quoteAcceptsChanges($item->quote);
    }

    /**
     * Check if the line is soft-deleted and can be permanently deleted.
     */
    public function canBeForceDeleted(QuoteItem $item): bool
    {
        return $this->isTrashed($item);
    }

    /**
     * Check if the user can force-delete based on role and line state.
     */
    public function canUserPerformAction(
        User $actor,
        string $action,
        QuoteItem $target,
    ): bool {
        return match ($action) {
            'restoreOrForceDelete' => $this->roleChecker->isAdmin($actor) && $this->canBeForceDeleted($target),
            default => false,
        };
    }
}
