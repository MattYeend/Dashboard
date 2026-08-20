<?php

namespace App\Services\System;

use App\Actions\System\ClearApplicationCache;
use App\Actions\System\DisableMaintenanceMode;
use App\Actions\System\EnableMaintenanceMode;
use App\Actions\System\GetSystemInfo;
use App\Models\Log;
use App\Models\User;
use App\Services\AuditLogService;

class SystemManagementService
{
    public function __construct(
        protected ClearApplicationCache $clearApplicationCache,
        protected EnableMaintenanceMode $enableMaintenanceMode,
        protected DisableMaintenanceMode $disableMaintenanceMode,
        protected GetSystemInfo $getSystemInfo,
        protected AuditLogService $auditLogService,
    ) {}

    /**
     * Get a snapshot of the current system state.
     *
     * @return array<string, mixed>
     */
    public function getSystemInfo(): array
    {
        return $this->getSystemInfo->handle();
    }

    /**
     * Clear the application cache and record the action.
     */
    public function clearCache(User $actor): void
    {
        $this->clearApplicationCache->handle();

        $this->auditLogService->record(
            actionId: Log::ACTION_CLEAR_CACHE,
            actor: $actor,
        );
    }

    /**
     * Enable maintenance mode and record the action.
     *
     * @param  array<string, mixed>  $options
     */
    public function enableMaintenanceMode(User $actor, array $options = []): void
    {
        $this->enableMaintenanceMode->handle($options);

        $this->auditLogService->record(
            actionId: Log::ACTION_ENABLE_MAINTENANCE,
            actor: $actor,
            data: ['options' => $options],
        );
    }

    /**
     * Disable maintenance mode and record the action.
     */
    public function disableMaintenanceMode(User $actor): void
    {
        $this->disableMaintenanceMode->handle();

        $this->auditLogService->record(
            actionId: Log::ACTION_DISABLE_MAINTENANCE,
            actor: $actor,
        );
    }
}
