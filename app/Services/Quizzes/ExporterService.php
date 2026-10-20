<?php

declare(strict_types=1);

namespace App\Services\Quizzes;

use App\Models\Log;
use App\Models\Quiz;
use App\Services\AuditLogService;
use Illuminate\Support\Carbon;
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
        'title',
        'slug',
        'course_id',
        'lesson_id',
        'pass_mark_percentage',
        'time_limit_minutes',
        'max_attempts',
        'shuffle_questions',
        'show_correct_answers',
        'published_at',
        'created_by',
        'created_at',
    ];

    /**
     * Inject the required services into the exporter service.
     */
    public function __construct(
        protected readonly AuditLogService $auditLogService,
        protected readonly FilterService $filterService,
    ) {}

    /**
     * Stream all matching quizzes as a CSV download.
     *
     * @param  array<string, mixed>  $filters
     */
    public function export(array $filters): StreamedResponse
    {
        $query = Quiz::query();

        if (($filters['trashed'] ?? null) === 'with') {
            $query->withTrashed();
        }

        $query = $this->filterService->applyAll($query, $filters);

        $this->auditLogService->record(
            Log::ACTION_EXPORT_QUIZ,
            Auth::user(),
            null,
            ['filters' => $filters, 'count' => (clone $query)->count()],
        );

        $callback = function () use ($query): void {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, self::COLUMNS);

            $query->orderBy('id')->chunk(500, function ($quizzes) use ($handle): void {
                foreach ($quizzes as $quiz) {
                    fputcsv($handle, $this->row($quiz));
                }
            });

            fclose($handle);
        };

        return response()->streamDownload(
            $callback,
            'quizzes-'.now()->format('Y-m-d-His').'.csv',
            ['Content-Type' => 'text/csv']
        );
    }

    /**
     * Build a single CSV row with every value formatted and neutralised.
     *
     * @return array<int, string>
     */
    private function row(Quiz $quiz): array
    {
        return array_map(
            fn (string $column): string => $this->neutralise($this->stringify($quiz->{$column})),
            self::COLUMNS
        );
    }

    /**
     * Convert a model value to a plain string for CSV output.
     */
    private function stringify(mixed $value): string
    {
        return match (true) {
            $value === null => '',
            is_bool($value) => $value ? '1' : '0',
            $value instanceof Carbon => $value->toIso8601String(),
            default => (string) $value,
        };
    }

    /**
     * Prevent CSV formula injection when the file is opened in a spreadsheet.
     */
    private function neutralise(string $value): string
    {
        if ($value === '') {
            return $value;
        }

        return in_array($value[0], ['=', '+', '-', '@', "\t", "\r"], true)
            ? "'".$value
            : $value;
    }
}
