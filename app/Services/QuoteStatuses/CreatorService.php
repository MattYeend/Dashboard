<?php

declare(strict_types=1);

namespace App\Services\QuoteStatuses;

use App\Actions\CreateResource;
use App\Models\Log;
use App\Models\QuoteStatus;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class CreatorService
{
    /**
     * Inject the required services into the creator service.
     */
    public function __construct(
        protected readonly DataPreparationService $dataPreparation,
        protected readonly AuditLogService $auditLogService,
        protected readonly CreateResource $createResource,
    ) {}

    /**
     * Create a new quote status.
     *
     * @param  array<string, mixed>  $data
     *
     * @throws ModelNotFoundException
     */
    public function create(array $data, int $createdBy): QuoteStatus
    {
        $actor = User::findOrFail($createdBy);

        return $this->createResource->handle(
            $data,
            function (array $data) use ($createdBy, $actor): QuoteStatus {
                $quoteStatusData = $this->dataPreparation->prepareForCreation($data);

                $newQuoteStatus = QuoteStatus::create($quoteStatusData);

                $newQuoteStatus->forceFill([
                    'created_by' => $createdBy,
                ])->save();

                $this->auditLogService->record(
                    Log::ACTION_CREATE_QUOTE_STATUS,
                    $actor,
                    $newQuoteStatus,
                    ['after' => $this->auditLogService->snapshot($newQuoteStatus)],
                );

                return $newQuoteStatus;
            });
    }
}