<?php

namespace App\Services\Organisations;

use App\Models\Log;
use App\Models\Organisation;
use App\Models\User;
use App\Services\AuditLogService;

class SettingsUpdaterService
{
    /**
     * Inject the required services into the settings updater service.
     */
    public function __construct(
        protected readonly AuditLogService $auditLogService,
    ) {}

    /**
     * Update an organisation's settings payload.
     *
     * Settings are merged rather than replaced wholesale, so the branding
     * form and the preferences form can each submit independently without
     * clobbering the other's fields.
     *
     * @param  array<string, mixed>  $data
     */
    public function update(
        Organisation $organisation,
        array $data,
        int $updatedBy
    ): Organisation {
        $actor = User::findOrFail($updatedBy);

        $before = $this->auditLogService->snapshot($organisation);

        $settings = array_merge($organisation->settings ?? [], $data);

        $organisation->forceFill([
            'settings' => $settings,
            'updated_by' => $updatedBy,
        ])->save();

        $fresh = $organisation->fresh();

        $this->auditLogService->record(
            Log::ACTION_UPDATE_ORGANISATION_SETTINGS,
            $actor,
            $fresh,
            [
                'before' => $before,
                'after' => $this->auditLogService->snapshot($fresh),
            ],
        );

        return $fresh;
    }
}
