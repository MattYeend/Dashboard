<?php

namespace App\Services\Reports;

use App\Actions\UpdateResource;
use App\Models\Log;
use App\Models\Report;
use App\Models\User;
use App\Services\AuditLogService;

class UpdaterService
{
    /**
     * Inject the required services into the updater service.
     */
    public function __construct(
        protected readonly DataPreparationService $dataPreparation,
        protected readonly AuditLogService $auditLogService,
        protected readonly UpdateResource $updateResource,
    ) {}

    /**
     * Update an existing report.
     *
     * @param  array<string, mixed>  $data
     *
     * @throws \Exception
     */
    public function update(
        Report $report,
        array $data,
        int $updatedBy
    ): Report {
        $actor = User::findOrFail($updatedBy);

        $before = $this->auditLogService->snapshot($report);

        $reportData = $this->dataPreparation->prepareForUpdate($data);

        return $this->updateResource->handle(
            $report,
            $reportData,
            function (Report $report) use ($actor, $before, $updatedBy): void {
                $report->forceFill([
                    'updated_by' => $updatedBy,
                ])->save();

                $fresh = $report->fresh();

                $this->auditLogService->record(
                    Log::ACTION_UPDATE_REPORT,
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
