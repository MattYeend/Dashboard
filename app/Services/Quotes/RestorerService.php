<?php

declare(strict_types=1);

namespace App\Services\Quotes;

use App\Actions\RestoreResource;
use App\Models\Log;
use App\Models\Quote;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class RestorerService
{
    /**
     * Inject the required services into the restorer service.
     */
    public function __construct(
        protected readonly AuditLogService $auditLogService,
        protected readonly RestoreResource $restoreResource,
    ) {}

    /**
     * Restore a soft-deleted quote.
     *
     * @throws \Exception
     */
    public function restore(
        Quote $quote,
        int $restoredBy,
        ?User $actor = null
    ): Quote {
        $actor ??= User::findOrFail($restoredBy);

        return $this->restoreResource->handle(
            $quote,
            function (Quote $quote) use ($actor, $restoredBy): void {
                $quote->restored_by = $restoredBy;
                $quote->restored_at = now();
                $quote->save();

                $this->auditLogService->record(
                    Log::ACTION_RESTORE_QUOTE,
                    $actor,
                    $quote,
                    ['before' => $this->auditLogService->snapshot($quote)],
                );
            });
    }

    /**
     * Restore multiple soft-deleted quotes.
     *
     * @param  array<int, int>  $quoteIds
     * @return int Number of quotes restored
     *
     * @throws \Exception
     */
    public function restoreMultiple(
        array $quoteIds,
        int $restoredBy
    ): int {
        $count = 0;

        DB::transaction(function () use ($quoteIds, $restoredBy, &$count) {
            $actor = User::findOrFail($restoredBy);

            /** @var Collection<int,Quote> $quotes */
            $quotes = Quote::withTrashed()
                ->whereIn('id', $quoteIds)
                ->get();

            foreach ($quotes as $quote) {
                if ($quote->trashed()) {
                    $this->restore($quote, $restoredBy, $actor);
                    $count++;
                }
            }
        });

        return $count;
    }
}
