<?php

namespace App\Services\Settings;

use App\Actions\UpdateResource;
use App\Models\Log;
use App\Models\Setting;
use App\Models\User;
use App\Services\AuditLogService;

class UpdaterService
{
    /**
     * Inject the required services into the updater service.
     */
    public function __construct(
        protected readonly QueryService $queryService,
        protected readonly AuditLogService $auditLogService,
        protected readonly UpdateResource $updateResource,
    ) {}

    /**
     * Update the general settings group.
     *
     * @param  array<string, mixed>  $data
     *
     * @throws \Exception
     */
    public function updateGeneral(array $data, int $updatedBy): Setting
    {
        return $this->apply(
            $data, 
            $updatedBy, 
            Log::ACTION_UPDATE_GENERAL_SETTINGS
        );
    }

    /**
     * Update the system settings group.
     *
     * @param  array<string, mixed>  $data
     *
     * @throws \Exception
     */
    public function updateSystem(array $data, int $updatedBy): Setting
    {
        return $this->apply(
            $data, 
            $updatedBy, 
            Log::ACTION_UPDATE_SYSTEM_SETTINGS
        );
    }

    /**
     * Update the security settings group.
     *
     * @param  array<string, mixed>  $data
     *
     * @throws \Exception
     */
    public function updateSecurity(array $data, int $updatedBy): Setting
    {
        return $this->apply(
            $data, 
            $updatedBy, 
            Log::ACTION_UPDATE_SECURITY_SETTINGS
        );
    }

    /**
     * Apply a group-scoped update to the singleton settings row and
     * write the corresponding audit log entry.
     *
     * @param  array<string, mixed>  $data
     *
     * @throws \Exception
     */
    protected function apply(array $data, int $updatedBy, int $logAction): Setting
    {
        $actor = User::findOrFail($updatedBy);
        $setting = $this->queryService->current();
        $before = $this->auditLogService->snapshot($setting);

        return $this->updateResource->handle(
            $setting,
            $data,
            function (Setting $setting) use ($actor, $before, $updatedBy, $logAction): void {
                $setting->forceFill([
                    'updated_by' => $updatedBy,
                ])->save();
                $fresh = $setting->fresh();

                $this->auditLogService->record(
                    $logAction,
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
