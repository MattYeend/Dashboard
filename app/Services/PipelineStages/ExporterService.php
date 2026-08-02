<?php

namespace App\Services\PipelineStages;

use App\Models\Log;
use App\Models\Pipeline;
use App\Services\AuditLogService;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExporterService
{
    /**
     * Inject the audit log service.
     */
    public function __construct(
        protected readonly AuditLogService $auditLogService,
    ) {}

    /**
     * Stream all stages belonging to a single pipeline as a CSV download.
     *
     * @param  array<string, mixed>  $filters
     */
    public function export(Pipeline $pipeline, array $filters): StreamedResponse
    {
        $query = $pipeline->stages();

        if (! empty($filters['trashed']) && $filters['trashed'] === 'with') {
            $query->withTrashed();
        }

        $columns = [
            'id', 'pipeline_id', 'title', 'description', 'position',
            'background_colour', 'text_colour', 'is_won', 'is_lost', 'created_at',
        ];

        $this->auditLogService->record(
            Log::ACTION_EXPORT_PIPELINE_STAGE,
            Auth::user(),
            null,
            ['pipeline_id' => $pipeline->id, 'filters' => $filters, 'count' => (clone $query)->count()],
        );

        $callback = function () use ($query, $columns) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $columns);

            $query->orderBy('position')->chunk(500, function ($stages) use ($handle, $columns) {
                foreach ($stages as $stage) {
                    fputcsv($handle, $stage->only($columns));
                }
            });

            fclose($handle);
        };

        return response()->streamDownload(
            $callback,
            'pipeline-'.$pipeline->id.'-stages-'.now()->format('Y-m-d-His').'.csv',
            ['Content-Type' => 'text/csv']
        );
    }
}
