<?php

namespace App\Services\Labels;

use App\Models\Label;
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
     * Stream all matching labels as a CSV download.
     *
     * @param  array<string, mixed>  $filters
     */
    public function export(array $filters): StreamedResponse
    {
        $query = Label::query();

        if (! empty($filters['trashed']) && $filters['trashed'] === 'with') {
            $query->withTrashed();
        }

        if (! empty($filters['search'])) {
            $search = $filters['search'];

            $query->where(function ($inner) use ($search) {
                $inner->where('name', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        $columns = [
            'id',
            'name',
            'slug',
            'background_colour',
            'text_colour',
            'created_at',
        ];

        $this->auditLogService->record(
            Log::ACTION_EXPORT_LABEL,
            Auth::user(),
            null,
            ['filters' => $filters, 'count' => (clone $query)->count()],
        );

        $callback = function () use ($query, $columns) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $columns);

            $query->orderBy('id')->chunk(500, function ($labels) use ($handle, $columns) {
                foreach ($labels as $label) {
                    fputcsv($handle, $label->only($columns));
                }
            });

            fclose($handle);
        };

        return response()->streamDownload(
            $callback,
            'labels-'.now()->format('Y-m-d-His').'.csv',
            ['Content-Type' => 'text/csv']
        );
    }
}
