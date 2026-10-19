<?php

declare(strict_types=1);

namespace App\Services\Lessons;

use App\Models\Lesson;
use App\Models\Log;
use App\Models\User;
use App\Services\AuditLogService;
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
     * Stream all matching lessons as a CSV download.
     *
     * @param  array<string, mixed>  $filters
     */
    public function export(array $filters, User $actor): StreamedResponse
    {
        $query = Lesson::query()->where('organisation_id', $actor->organisation_id);

        if (($filters['trashed'] ?? null) === 'with') {
            $query->withTrashed();
        }

        $query = $this->filterService->applyAll($query, $filters);

        $columns = [
            'id',
            'course_id',
            'title',
            'slug',
            'position',
            'duration_minutes',
            'is_preview',
            'published_at',
            'created_by',
            'created_at',
        ];

        $this->auditLogService->record(
            Log::ACTION_EXPORT_LESSON,
            $actor,
            null,
            ['filters' => $filters, 'count' => (clone $query)->count(), 'action' => 'export'],
        );

        $callback = function () use ($query, $columns) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $columns);

            $query->orderBy('id')->chunk(500, function ($lessons) use ($handle, $columns) {
                foreach ($lessons as $lesson) {
                    fputcsv($handle, array_map(
                        fn (mixed $value) => $this->sanitiseCell($value),
                        $lesson->only($columns)
                    ));
                }
            });

            fclose($handle);
        };

        return response()->streamDownload(
            $callback,
            'lessons-'.now()->format('Y-m-d-His').'.csv',
            ['Content-Type' => 'text/csv']
        );
    }

    /**
     * Neutralise spreadsheet formula injection and normalise booleans.
     */
    protected function sanitiseCell(mixed $value): mixed
    {
        if (is_bool($value)) {
            return (int) $value;
        }

        if (! is_string($value) || $value === '') {
            return $value;
        }

        return in_array($value[0], ['=', '+', '-', '@', "\t", "\r"], true)
            ? "'".$value
            : $value;
    }
}
