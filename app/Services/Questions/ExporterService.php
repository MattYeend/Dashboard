<?php

declare(strict_types=1);

namespace App\Services\Questions;

use App\Models\Log;
use App\Models\Question;
use App\Services\AuditLogService;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExporterService
{
    /**
     * Columns written to the CSV, in order.
     *
     * @var array<int, string>
     */
    protected const COLUMNS = [
        'id',
        'questionable_type',
        'questionable_id',
        'question',
        'type',
        'explanation',
        'points',
        'position',
        'is_required',
        'created_by',
        'created_at',
    ];

    /**
     * Inject the required services into the exporter service.
     */
    public function __construct(
        protected readonly FilterService $filterService,
        protected readonly AuditLogService $auditLogService,
    ) {}

    /**
     * Stream all matching questions as a CSV download.
     *
     * @param  array<string, mixed>  $filters
     */
    public function export(array $filters): StreamedResponse
    {
        $query = Question::query();

        if (($filters['trashed'] ?? null) === 'with') {
            $query->withTrashed();
        }

        $query = $this->filterService->applyAll($query, $filters);

        $this->auditLogService->record(
            Log::ACTION_EXPORT_QUESTION,
            Auth::user(),
            null,
            ['filters' => $filters, 'count' => (clone $query)->count()],
        );

        $callback = function () use ($query) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, self::COLUMNS);

            $query->orderBy('id')->chunkById(500, function ($questions) use ($handle) {
                foreach ($questions as $question) {
                    fputcsv($handle, $this->row($question));
                }
            });

            fclose($handle);
        };

        return response()->streamDownload(
            $callback,
            'questions-'.now()->format('Y-m-d-His').'.csv',
            ['Content-Type' => 'text/csv']
        );
    }

    /**
     * Build a single, sanitised CSV row.
     *
     * @return array<int, scalar|null>
     */
    private function row(Question $question): array
    {
        return array_map(
            fn (string $column) => $this->sanitise($this->value($question, $column)),
            self::COLUMNS,
        );
    }

    /**
     * Get the CSV value for a column.
     */
    private function value(Question $question, string $column): mixed
    {
        return match ($column) {
            'type' => $question->type->value,
            'is_required' => $question->is_required ? 1 : 0,
            default => $question->{$column},
        };
    }

    /**
     * Prefix values that a spreadsheet could interpret as a formula.
     */
    private function sanitise(mixed $value): mixed
    {
        if (is_string($value) && preg_match('/^[=+\-@\t\r]/', $value) === 1) {
            return "'".$value;
        }

        return $value;
    }
}
