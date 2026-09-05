<?php

namespace App\Services\Reports;

use App\Actions\CreateResource;
use App\Models\Log;
use App\Models\Report;
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
     * Create a new report.
     *
     * @param  array<string, mixed>  $data
     *
     * @throws ModelNotFoundException
     */
    public function create(array $data, int $createdBy): Report
    {
        $actor = User::findOrFail($createdBy);

        return $this->createResource->handle(
            $data,
            function (array $data) use ($createdBy, $actor): Report {
                $reportData = $this->dataPreparation->prepareForCreation($data);

                $newReport = Report::create($reportData);

                $newReport->forceFill([
                    'created_by' => $createdBy,
                ])->save();

                $this->auditLogService->record(
                    Log::ACTION_CREATE_REPORT,
                    $actor,
                    $newReport,
                    ['after' => $this->auditLogService->snapshot($newReport)],
                );

                return $newReport;
            });
    }
}
