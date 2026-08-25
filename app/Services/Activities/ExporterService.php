<?php

namespace App\Services\Activities;

use App\Models\Activity;
use App\Models\Log;
use App\Services\AuditLogService;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExporterService
{
    /**
     * Inject the required services into the exporter.
     */
    public function __construct(
        protected readonly QueryService $queryService,
        protected readonly FormatterService $formatterService,
        protected readonly AuditLogService $auditLogService,
        protected readonly ActivityableTypeRegistryService $registry,
    ) {}

    /**
     * Stream all matching activities as a CSV download.
     *
     * @param  array<string, mixed>  $filters
     */
    public function export(
        ?string $activityableType,
        ?int $activityableId,
        array $filters = []
    ): StreamedResponse {
        $resolvedType = $activityableType
            ? $this->registry->modelClassForKey($activityableType)
            : null;

        $query = $resolvedType && $activityableId
            ? Activity::query()
                ->with('creator')
                ->where('activityable_type', $resolvedType)
                ->where('activityable_id', $activityableId)
            : $this->queryService->forExportAll($filters);

        $columns = ['ID', 'Type', 'Description', 'Occurred at', 'Logged by'];

        $this->auditLogService->record(
            Log::ACTION_EXPORT_ACTIVITY,
            Auth::user(),
            null,
            [
                'scope' => $resolvedType && $activityableId ? "{$resolvedType}:{$activityableId}" : 'all',
                'filters' => $filters,
                'count' => (clone $query)->count(),
            ],
        );

        $callback = function () use ($query, $columns) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $columns);

            $query->orderBy('id')->chunk(200, function ($activities) use ($handle) {
                foreach ($activities as $activity) {
                    fputcsv($handle, array_values($this->formatterService->forExport($activity)));
                }
            });

            fclose($handle);
        };

        return response()->streamDownload(
            $callback,
            'activities-'.now()->format('Y-m-d-His').'.csv',
            ['Content-Type' => 'text/csv']
        );
    }
}
