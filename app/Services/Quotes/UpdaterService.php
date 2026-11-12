<?php

declare(strict_types=1);

namespace App\Services\Quotes;

use App\Actions\UpdateResource;
use App\Models\Log;
use App\Models\Quote;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Auth\Access\AuthorizationException;

class UpdaterService
{
    /**
     * Inject the required services into the updater service.
     */
    public function __construct(
        protected readonly DataPreparationService $dataPreparation,
        protected readonly ActiveCheckerService $activeChecker,
        protected readonly AuditLogService $auditLogService,
        protected readonly UpdateResource $updateResource,
    ) {}

    /**
     * Update an existing quote. Only Draft quotes can be edited.
     *
     * @param  array<string, mixed>  $data
     *
     * @throws AuthorizationException
     * @throws \Exception
     */
    public function update(
        Quote $quote,
        array $data,
        int $updatedBy
    ): Quote {
        // Second layer: Super Admin bypasses the policy through Gate::before.
        if (! $this->activeChecker->isEditable($quote)) {
            throw new AuthorizationException('Only draft quotes can be edited.');
        }

        $actor = User::findOrFail($updatedBy);

        $before = $this->auditLogService->snapshot($quote);

        $quoteData = $this->dataPreparation->prepareForUpdate($data, $quote);

        return $this->updateResource->handle(
            $quote,
            $quoteData,
            function (Quote $quote) use ($actor, $before, $updatedBy): void {
                $quote->forceFill([
                    'updated_by' => $updatedBy,
                ])->save();

                $fresh = $quote->fresh();

                $this->auditLogService->record(
                    Log::ACTION_UPDATE_QUOTE,
                    $actor,
                    $fresh,
                    [
                        'before' => $before,
                        'after' => $this->auditLogService->snapshot($fresh),
                    ],
                );
            });
    }
}
