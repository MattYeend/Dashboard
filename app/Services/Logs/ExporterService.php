<?php

namespace App\Services\Logs;

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
     * Stream all matching activity logs as a CSV download.
     *
     * @param  array<string, mixed>  $filters
     */
    public function export(array $filters): StreamedResponse
    {
        $query = Log::query()->with(['loggedInUser', 'relatedToUser']);

        if (! empty($filters['action'])) {
            $query->ofAction((int) $filters['action']);
        }

        if (! empty($filters['logged_in_user_id'])) {
            $query->where('logged_in_user_id', (int) $filters['logged_in_user_id']);
        }

        if (! empty($filters['related_to_user_id'])) {
            $query->where('related_to_user_id', (int) $filters['related_to_user_id']);
        }

        if (! empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (! empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        $this->auditLogService->record(
            Log::ACTION_EXPORT_ACTIVITY_LOG,
            Auth::user(),
            null,
            ['filters' => $filters, 'count' => (clone $query)->count()],
        );

        $callback = function () use ($query) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['ID', 'Action', 'Performed by', 'Related to', 'Details', 'Date']);

            $query->orderBy('id')->chunk(500, function ($logs) use ($handle) {
                foreach ($logs as $log) {
                    fputcsv($handle, [
                        $log->id,
                        Log::actionLabel($log->action_id),
                        $log->loggedInUser?->name ?? 'System',
                        $log->relatedToUser?->name ?? '',
                        json_encode($log->data),
                        $log->created_at,
                    ]);
                }
            });

            fclose($handle);
        };

        return response()->streamDownload(
            $callback,
            'activity-logs-'.now()->format('Y-m-d-His').'.csv',
            ['Content-Type' => 'text/csv']
        );
    }
}
