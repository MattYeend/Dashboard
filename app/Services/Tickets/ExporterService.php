<?php

namespace App\Services\Tickets;

use App\Models\Log;
use App\Models\Ticket;
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
     * Stream all matching tickets as a CSV download.
     *
     * @param  array<string, mixed>  $filters
     */
    public function export(array $filters): StreamedResponse
    {
        $query = Ticket::query()->with([
            'status',
            'priority',
            'assignee',
            'labels',
        ]);

        if (! empty($filters['trashed']) && $filters['trashed'] === 'with') {
            $query->withTrashed();
        }

        if (! empty($filters['search'])) {
            $search = $filters['search'];

            $query->where(function ($inner) use ($search) {
                $inner->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $columns = [
            'id',
            'title',
            'description',
            'status',
            'priority',
            'assignee',
            'due_date',
            'resolved_at',
            'labels',
            'created_by',
            'created_at',
        ];

        $this->auditLogService->record(
            Log::ACTION_EXPORT_TICKET,
            Auth::user(),
            null,
            ['filters' => $filters, 'count' => (clone $query)->count()],
        );

        $callback = function () use ($query, $columns) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $columns);

            $query->orderBy('id')->chunk(500, function ($tickets) use ($handle) {
                foreach ($tickets as $ticket) {
                    fputcsv($handle, [
                        $ticket->id,
                        $ticket->title,
                        $ticket->description,
                        $ticket->status?->title,
                        $ticket->priority?->title,
                        $ticket->assignee?->name,
                        $ticket->due_date,
                        $ticket->resolved_at,
                        $ticket->labels->pluck('name')->implode(', '),
                        $ticket->created_at,
                    ]);
                }
            });

            fclose($handle);
        };

        return response()->streamDownload(
            $callback,
            'tickets-'.now()->format('Y-m-d-His').'.csv',
            ['Content-Type' => 'text/csv']
        );
    }
}
