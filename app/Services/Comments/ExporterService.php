<?php

namespace App\Services\Comments;

use App\Models\Comment;
use App\Models\Log;
use App\Services\AuditLogService;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExporterService
{
    /**
     * Inject the required services into the exporter service.
     */
    public function __construct(
        protected readonly AuditLogService $auditLogService,
        protected readonly FilterService $filterService,
    ) {}

    /**
     * Stream comments as a CSV download.
     *
     * @param  array<string, mixed>  $filters
     */
    public function export(array $filters): StreamedResponse
    {
        $query = Comment::query();

        if (($filters['trashed'] ?? null) === 'with') {
            $query->withTrashed();
        }

        $query = $this->filterService->applyAll($query, $filters);

        $columns = ['id', 'commentable_type', 'commentable_id', 'content', 'created_by', 'created_at'];

        $this->auditLogService->record(
            Log::ACTION_EXPORT_COMMENT,
            Auth::user(),
            null,
            ['filters' => $filters, 'count' => (clone $query)->count()],
        );

        $callback = function () use ($query, $columns) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $columns);

            $query->orderBy('id')->chunk(500, function ($comments) use ($handle, $columns) {
                foreach ($comments as $comment) {
                    fputcsv($handle, $comment->only($columns));
                }
            });

            fclose($handle);
        };

        return response()->streamDownload(
            $callback,
            'comments-'.now()->format('Y-m-d-His').'.csv',
            ['Content-Type' => 'text/csv']
        );
    }
}
