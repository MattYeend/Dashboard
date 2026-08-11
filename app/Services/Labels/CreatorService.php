<?php

namespace App\Services\Labels;

use App\Actions\CreateResource;
use App\Models\Label;
use App\Models\Log;
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
     * Create a new label.
     *
     * @param  array<string, mixed>  $data
     *
     * @throws ModelNotFoundException
     */
    public function create(array $data, int $createdBy): Label
    {
        $actor = User::findOrFail($createdBy);

        return $this->createResource->handle(
            $data,
            function (array $data) use ($createdBy, $actor): Label {
                $labelData = $this->dataPreparation->prepareForCreation($data, $createdBy);

                $newLabel = Label::create($labelData);

                $this->auditLogService->record(
                    Log::ACTION_CREATE_LABEL,
                    $actor,
                    $newLabel,
                    ['after' => $this->auditLogService->snapshot($newLabel)],
                );

                return $newLabel;
            });
    }
}
