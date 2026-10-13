<?php

declare(strict_types=1);

namespace App\Services\Courses;

use App\Models\Course;
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
     * Stream all matching courses as a CSV download.
     *
     * @param  array<string, mixed>  $filters
     */
    public function export(array $filters): StreamedResponse
    {
        $query = Course::query();

        if (($filters['trashed'] ?? null) === 'with') {
            $query->withTrashed();
        }

        $query = $this->filterService->applyAll($query, $filters);

        $columns = [
            'id',
            'title',
            'slug',
            'summary',
            'difficulty',
            'estimated_duration_minutes',
            'instructor_id',
            'published_at',
            'created_by',
            'created_at',
        ];

        $this->auditLogService->record(
            Log::ACTION_EXPORT_COURSE,
            Auth::user(),
            null,
            ['filters' => $filters, 'count' => (clone $query)->count()],
        );

        $callback = function () use ($query, $columns) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $columns);

            $query->orderBy('id')->chunk(500, function ($courses) use ($handle, $columns) {
                foreach ($courses as $course) {
                    fputcsv($handle, array_map(
                        fn ($value) => $this->sanitiseCell($value),
                        $course->only($columns)
                    ));
                }
            });

            fclose($handle);
        };

        return response()->streamDownload(
            $callback,
            'courses-'.now()->format('Y-m-d-His').'.csv',
            ['Content-Type' => 'text/csv']
        );
    }

    /**
     * Neutralise spreadsheet formula injection in a CSV cell.
     */
    private function sanitiseCell(mixed $value): mixed
    {
        if (! is_string($value) || $value === '') {
            return $value;
        }

        return in_array($value[0], ['=', '+', '-', '@', "\t", "\r"], true)
            ? "'".$value
            : $value;
    }
}
