<?php

namespace App\Services\TicketPriorities;

use App\Models\Log;
use App\Models\TicketPriority;
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
     * Stream all matching ticket priorities as a CSV download.
     *
     * @param  array<string, mixed>  $filters
     */
    public function export(array $filters): StreamedResponse
    {
        $query = TicketPriority::query();

        if (! empty($filters['trashed']) && $filters['trashed'] === 'with') {
            $query->withTrashed();
        }

        if (! empty($filters['search'])) {
            $search = $filters['search'];

            $query->where('title', 'like', "%{$search}%");
        }

        if (isset($filters['level']) && $filters['level'] !== '') {
            $query->where('level', (int) $filters['level']);
        }

        $columns = [
            'id',
            'title',
            'level',
            'background_colour',
            'text_colour',
            'created_at',
        ];

        $this->auditLogService->record(
            Log::ACTION_EXPORT_TICKET_PRIORITY,
            Auth::user(),
            null,
            ['filters' => $filters, 'count' => (clone $query)->count()],
        );

        $callback = function () use ($query, $columns) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $columns);

            $query->orderBy('level')->chunk(500, function ($ticketPriorities) use ($handle, $columns) {
                foreach ($ticketPriorities as $ticketPriority) {
                    fputcsv($handle, $ticketPriority->only($columns));
                }
            });

            fclose($handle);
        };

        return response()->streamDownload(
            $callback,
            'ticket-priorities-'.now()->format('Y-m-d-His').'.csv',
            ['Content-Type' => 'text/csv']
        );
    }
}
