<?php

declare(strict_types=1);

namespace App\Services\Quotes;

use App\Models\Quote;
use App\Models\QuoteStatus;
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
     * Check if quote is active (not soft-deleted).
     */
    public function isActive(Quote $quote): bool
    {
        return ! $quote->trashed();
    }

    /**
     * Check if quote is soft-deleted.
     */
    public function isTrashed(Quote $quote): bool
    {
        return $quote->trashed();
    }

    /**
     * Check if quote is active (not soft-deleted) and can be
     * updated/deleted.
     */
    public function canBeModified(Quote $quote): bool
    {
        return $this->isActive($quote);
    }

    /**
     * Check if quote is soft-deleted and can be restored/force-deleted.
     */
    public function canBeRestoredOrForceDeleted(
        Quote $quote
    ): bool {
        return $this->isTrashed($quote);
    }

    /**
     * Check if user can modify quote (update/delete) or restore/force-delete
     * quote based on its active status.
     */
    public function canUserPerformAction(
        User $actor,
        string $action,
        Quote $target,
    ): bool {
        return match ($action) {
            'modify' => $this->roleChecker->isAdmin($actor) && $this->isEditable($target),
            'restoreOrForceDelete' => $this->roleChecker->isAdmin($actor) && $this->canBeRestoredOrForceDeleted($target),
            default => false,
        };
    }

    /**
     * Only active Draft quotes can be edited.
     */
    public function isEditable(Quote $quote): bool
    {
        return $this->isActive($quote) && $quote->isStatus(QuoteStatus::KEY_DRAFT);
    }

    /**
     * Only active Draft, Declined or Expired quotes can be deleted.
     */
    public function canBeDeleted(Quote $quote): bool
    {
        if (! $this->isActive($quote)) {
            return false;
        }

        return $quote->isStatus(QuoteStatus::KEY_DRAFT)
            || $quote->isStatus(QuoteStatus::KEY_DECLINED)
            || $quote->isStatus(QuoteStatus::KEY_EXPIRED);
    }

    /**
     * Only an active Draft quote can be sent.
     */
    public function canBeSent(Quote $quote): bool
    {
        return $this->isEditable($quote);
    }

    /**
     * Only an active Sent quote can be accepted or declined.
     */
    public function canBeAnswered(Quote $quote): bool
    {
        return $this->isActive($quote) && $quote->isStatus(QuoteStatus::KEY_SENT);
    }

    /**
     * Only an active Accepted quote that has not been converted can convert.
     */
    public function canBeConverted(Quote $quote): bool
    {
        return $this->isActive($quote)
            && $quote->isStatus(QuoteStatus::KEY_ACCEPTED)
            && $quote->converted_order_id === null;
    }
}
