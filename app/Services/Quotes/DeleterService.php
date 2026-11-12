<?php

declare(strict_types=1);

namespace App\Services\Quotes;

use App\Actions\DeleteResource;
use App\Models\Log;
use App\Models\Quote;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;

class DeleterService
{
    /**
     * Inject the required services into the deleter service.
     */
    public function __construct(
        protected readonly AuditLogService $auditLogService,
        protected readonly ActiveCheckerService $activeChecker,
        protected readonly DeleteResource $deleteResource,
    ) {}

    /**
     * Soft delete a quote.
     *
     * @throws AuthorizationException
     * @throws \Exception
     */
    public function delete(
        Quote $quote,
        int $deletedBy,
        ?User $actor = null
    ): bool {
        if (! $this->activeChecker->canBeDeleted($quote)) {
            throw new AuthorizationException('Only draft, declined or expired quotes can be deleted.');
        }

        $actor ??= User::findOrFail($deletedBy);

        return $this->deleteResource->handle(
            $quote,
            function (Quote $quote) use ($actor, $deletedBy): void {
                $quote->deleted_by = $deletedBy;
                $quote->deleted_at = now();
                $quote->save();

                $this->auditLogService->record(
                    Log::ACTION_DELETE_QUOTE,
                    $actor,
                    $quote,
                    ['before' => $this->auditLogService->snapshot($quote)],
                );
            });
    }

    /**
     * Force delete a quote (permanent deletion).
     *
     * @throws \Exception
     */
    public function forceDelete(
        Quote $quote,
        int $deletedBy
    ): bool {
        $actor = User::findOrFail($deletedBy);

        return $this->deleteResource->forceHandle(
            $quote,
            function (Quote $quote) use ($actor): void {
                $this->auditLogService->record(
                    Log::ACTION_FORCE_DELETE_QUOTE,
                    $actor,
                    $quote,
                    ['before' => $this->auditLogService->snapshot($quote)],
                );
            });
    }

    /**
     * Delete multiple quotes.
     *
     * @param  array<int, int>  $quoteIds
     *
     * @throws AuthorizationException
     * @throws \Exception
     */
    public function deleteMultiple(
        array $quoteIds,
        int $deletedBy
    ): int {
        $count = 0;

        DB::transaction(function () use ($quoteIds, $deletedBy, &$count) {
            $actor = User::findOrFail($deletedBy);
            $quotes = Quote::whereIn('id', $quoteIds)->get();

            foreach ($quotes as $quote) {
                if ($this->delete($quote, $deletedBy, $actor)) {
                    $count++;
                }
            }
        });

        return $count;
    }
}
