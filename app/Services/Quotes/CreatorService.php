<?php

declare(strict_types=1);

namespace App\Services\Quotes;

use App\Actions\CreateResource;
use App\Models\Log;
use App\Models\Quote;
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
     * Create a new quote.
     *
     * @param  array<string, mixed>  $data
     *
     * @throws ModelNotFoundException
     */
    public function create(array $data, int $createdBy): Quote
    {
        $actor = User::findOrFail($createdBy);

        return $this->createResource->handle(
            $data,
            function (array $data) use ($createdBy, $actor): Quote {
                $quoteData = $this->dataPreparation->prepareForCreation($data);

                $newQuote = Quote::create($quoteData);

                $newQuote->forceFill([
                    'created_by' => $createdBy,
                ])->save();

                $this->auditLogService->record(
                    Log::ACTION_CREATE_QUOTE,
                    $actor,
                    $newQuote,
                    ['after' => $this->auditLogService->snapshot($newQuote)],
                );

                return $newQuote;
            });
    }
}
