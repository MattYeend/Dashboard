<?php

namespace App\Services\InteractionLogs;

use App\Http\Requests\InteractionLogs\StoreInteractionLogRequest;
use App\Http\Requests\InteractionLogs\UpdateInteractionLogRequest;

class DataPreparationService
{
    public function __construct(
        private readonly InteractionLoggableTypeRegistryService $registryService,
    ) {}

    /**
     * Prepare validated data for creating an interaction log.
     *
     * @return array<string, mixed>
     */
    public function prepareForCreate(StoreInteractionLogRequest $request): array
    {
        $data = $request->validated();

        $data['interactable_type'] = $this->registryService->modelClassForKey($data['interactable_type']);

        return $data;
    }

    /**
     * Prepare validated data for updating an interaction log.
     *
     * @return array<string, mixed>
     */
    public function prepareForUpdate(UpdateInteractionLogRequest $request): array
    {
        return $request->validated();
    }
}
