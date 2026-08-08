<?php

namespace App\Services\TicketStatuses;

use App\Models\Log;
use App\Models\TicketStatus;
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
     * Stream all matching ticket statuses as a CSV download.
     *
     * @param  array<string, mixed>  $filters
     */
    public function export(array $filters): StreamedResponse
    {
        $query = TicketStatus::query();

        if (! empty($filters['trashed']) && $filters['trashed'] === 'with') {
            $query->withTrashed();
        }

        if (! empty($filters['search'])) {
            $search = $filters['search'];

            $query->where('title', 'like', "%{$search}%");
        }

        $columns = [
            'id', 
            'title', 
            'description', 
            'background_colour', 
            'text_colour', 
            'created_at',
        ];

        $this->auditLogService->record(
            Log::ACTION_EXPORT_TICKET_STATUS,
            Auth::user(),
            null,
            ['filters' => $filters, 'count' => (clone $query)->count()],
        );

        $callback = function () use ($query, $columns) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $columns);

            $query->orderBy('id')->chunk(500, function ($ticketStatuses) use ($handle, $columns) {
                foreach ($ticketStatuses as $ticketStatus) {
                    fputcsv($handle, $ticketStatus->only($columns));
                }
            });

            fclose($handle);
        };

        return response()->streamDownload(
            $callback,
            'ticket-statuses-'.now()->format('Y-m-d-His').'.csv',
            ['Content-Type' => 'text/csv']
        );
    }
}
