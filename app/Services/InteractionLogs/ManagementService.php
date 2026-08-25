<?php

namespace App\Services\InteractionLogs;

use App\Http\Requests\InteractionLogs\StoreInteractionLogRequest;
use App\Http\Requests\InteractionLogs\UpdateInteractionLogRequest;
use App\Models\InteractionLog;
use App\Models\User;

class ManagementService
{
    /**
     * Inject the required services into the management service.
     */
    public function __construct(
        protected readonly CreatorService $creatorService,
        protected readonly UpdaterService $updaterService,
        protected readonly DeleterService $deleterService,
        protected readonly DataPreparationService $dataPreparationService,
        protected readonly FormatterService $formatterService,
    ) {}

    /**
     * Store a new interaction log from the given request.
     *
     * @return array<string, mixed>
     */
    public function store(StoreInteractionLogRequest $request): array
    {
        $actor = User::findOrFail($request->user()->id);
        $data = $this->dataPreparationService->prepareForCreate($request);

        $interactionLog = $this->creatorService->create($data, $actor->id);

        return $this->formatterService->format($interactionLog);
    }

    /**
     * Update an existing interaction log from the given request.
     *
     * @return array<string, mixed>
     */
    public function update(
        UpdateInteractionLogRequest $request,
        InteractionLog $interactionLog
    ): array {
        $actor = User::findOrFail($request->user()->id);
        $data = $this->dataPreparationService->prepareForUpdate($request);

        $interactionLog = $this->updaterService->update(
            $interactionLog,
            $data,
            $actor->id
        );

        return $this->formatterService->format($interactionLog);
    }

    /**
     * Soft delete an interaction log.
     */
    public function delete(
        InteractionLog $interactionLog,
        User $actor
    ): void {
        $this->deleterService->delete(
            $interactionLog,
            $actor->id
        );
    }

    /**
     * Permanently delete an interaction log.
     */
    public function forceDelete(
        InteractionLog $interactionLog,
        User $actor
    ): void {
        $this->deleterService->forceDelete(
            $interactionLog,
            $actor->id
        );
    }
}
