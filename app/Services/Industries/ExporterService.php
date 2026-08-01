<?php

namespace App\Services\Industries;

use App\Models\Industry;
use App\Models\Log;
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
     * Stream all matching industries as a CSV download.
     *
     * @param  array<string, mixed>  $filters
     */
    public function export(array $filters): StreamedResponse
    {
        $query = Industry::query();

        if (! empty($filters['trashed']) && $filters['trashed'] === 'with') {
            $query->withTrashed();
        }

        if (! empty($filters['search'])) {
            $search = $filters['search'];

            $query->where(function ($inner) use ($search) {
                $inner->where('title', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $columns = [
            'id', 'title', 'code', 'description', 'created_at',
        ];

        $this->auditLogService->record(
            Log::ACTION_EXPORT_INDUSTRY,
            Auth::user(),
            null,
            ['filters' => $filters, 'count' => (clone $query)->count()],
        );

        $callback = function () use ($query, $columns) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $columns);

            $query->orderBy('id')->chunk(500, function ($industries) use ($handle, $columns) {
                foreach ($industries as $industry) {
                    fputcsv($handle, $industry->only($columns));
                }
            });

            fclose($handle);
        };

        return response()->streamDownload(
            $callback,
            'industries-'.now()->format('Y-m-d-His').'.csv',
            ['Content-Type' => 'text/csv']
        );
    }
}