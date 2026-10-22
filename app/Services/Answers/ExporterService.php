<?php

declare(strict_types=1);

namespace App\Services\Answers;

use App\Models\Answer;
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
     * Stream all matching answers as a CSV download.
     *
     * The controller must only call this for users who may view correct
     * answers, as the export contains the answer key.
     *
     * @param  array<string, mixed>  $filters
     */
    public function export(array $filters): StreamedResponse
    {
        $query = Answer::query();

        $query = match ($filters['trashed'] ?? null) {
            'with' => $query->withTrashed(),
            'only' => $query->onlyTrashed(),
            default => $query,
        };

        $query = $this->filterService->applyAll($query, $filters);

        $columns = [
            'id',
            'question_id',
            'answer',
            'is_correct',
            'feedback',
            'position',
            'created_by',
            'created_at',
        ];

        $this->auditLogService->record(
            Log::ACTION_EXPORT_ANSWER,
            Auth::user(),
            null,
            ['filters' => $filters, 'count' => (clone $query)->count()],
        );

        $callback = function () use ($query, $columns) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $columns);

            $query->orderBy('id')->chunk(500, function ($answers) use ($handle) {
                foreach ($answers as $answer) {
                    fputcsv($handle, [
                        $answer->id,
                        $answer->question_id,
                        $this->sanitise($answer->answer),
                        $answer->is_correct ? 1 : 0,
                        $this->sanitise($answer->feedback),
                        $answer->position,
                        $answer->created_by,
                        $answer->created_at?->toDateTimeString(),
                    ]);
                }
            });

            fclose($handle);
        };

        return response()->streamDownload(
            $callback,
            'answers-'.now()->format('Y-m-d-His').'.csv',
            ['Content-Type' => 'text/csv']
        );
    }

    /**
     * Neutralise spreadsheet formula injection in a CSV cell.
     */
    private function sanitise(?string $value): ?string
    {
        if ($value === null || $value === '') {
            return $value;
        }

        return in_array($value[0], ['=', '+', '-', '@', "\t", "\r"], true)
            ? "'".$value
            : $value;
    }
}
