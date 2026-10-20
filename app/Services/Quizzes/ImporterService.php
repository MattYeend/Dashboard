<?php

declare(strict_types=1);

namespace App\Services\Quizzes;

use App\Models\Course;
use App\Models\Lesson;
use App\Models\Log;
use App\Models\Quiz;
use App\Models\User;
use App\Services\AuditLogService;
use App\Services\Concerns\ImportsViaPreview;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

/**
 * Handles CSV import of quizzes, including the preview/commit workflow
 * (via ImportsViaPreview) alongside the one-shot import() method.
 * Questions are not imported; attach them afterwards in the editor.
 * Imported quizzes have no published_at, so they always arrive as drafts.
 */
class ImporterService
{
    use ImportsViaPreview;

    /**
     * Column headers that must be present in the uploaded CSV.
     *
     * @var array<int, string>
     */
    protected const REQUIRED_COLUMNS = [
        'title',
        'course_id',
    ];

    /**
     * Inject the required services into the importer service.
     */
    public function __construct(
        protected readonly AuditLogService $auditLogService,
        protected readonly DataPreparationService $dataPreparation,
    ) {}

    /**
     * The private-disk directory this module's uploads are stored under.
     */
    protected function importStoragePath(): string
    {
        return 'imports/quizzes';
    }

    /**
     * The Log::ACTION_IMPORT_* constant for this module's batch log entry.
     */
    protected function importAuditAction(): int
    {
        return Log::ACTION_IMPORT_QUIZ;
    }

    /**
     * Persist a single validated row as a new Quiz.
     *
     * @param  array<string, mixed>  $data
     * @param  array<string, mixed>  $context
     */
    protected function persistRow(array $data, int $actorId, array $context = []): void
    {
        $this->createFromRow($data, $actorId);
    }

    /**
     * Import quizzes from an uploaded CSV file.
     *
     * @return array{imported: int, skipped: array<int, array{row: int, reason: string}>}
     */
    public function import(
        UploadedFile $file,
        int $actorId
    ): array {
        $handle = fopen($file->getRealPath(), 'r');

        if ($handle === false) {
            return [
                'imported' => 0,
                'skipped' => [['row' => 0, 'reason' => 'The uploaded file could not be read']],
            ];
        }

        $header = fgetcsv($handle);
        $header = array_map(
            fn (?string $column) => strtolower(trim((string) $column)),
            $header ?: []
        );

        $missing = array_diff(self::REQUIRED_COLUMNS, $header);

        if (! empty($missing)) {
            fclose($handle);

            return [
                'imported' => 0,
                'skipped' => [[
                    'row' => 0,
                    'reason' => 'Missing required column(s): '.implode(', ', $missing),
                ]],
            ];
        }

        $imported = 0;
        $skipped = [];
        $rowNumber = 1;
        $actor = User::findOrFail($actorId);

        DB::transaction(function () use ($handle, $header, $actor, $actorId, &$imported, &$skipped, &$rowNumber) {
            while (($row = fgetcsv($handle)) !== false) {
                $rowNumber++;

                if (count($row) !== count($header)) {
                    $skipped[] = [
                        'row' => $rowNumber,
                        'reason' => sprintf(
                            'Expected %d columns but found %d',
                            count($header),
                            count($row),
                        ),
                    ];

                    continue;
                }

                $data = array_combine($header, $row);

                $error = $this->validateRow($data);

                if ($error !== null) {
                    $skipped[] = ['row' => $rowNumber, 'reason' => $error];

                    continue;
                }

                $quiz = $this->createFromRow($data, $actorId);

                $this->auditLogService->record(
                    Log::ACTION_IMPORT_QUIZ,
                    $actor,
                    $quiz,
                    ['after' => $this->auditLogService->snapshot($quiz)],
                );

                $imported++;
            }
        });

        fclose($handle);

        return ['imported' => $imported, 'skipped' => $skipped];
    }

    /**
     * Validate a single row, returning an error string or null if valid.
     *
     * @param  array<string, mixed>  $data
     */
    protected function validateRow(array $data): ?string
    {
        foreach (self::REQUIRED_COLUMNS as $column) {
            if (! isset($data[$column]) || trim((string) $data[$column]) === '') {
                return "Missing value for '{$column}'";
            }
        }

        if (mb_strlen((string) $data['title']) > 255) {
            return 'title must not exceed 255 characters';
        }

        if (! ctype_digit((string) $data['course_id'])) {
            return 'course_id must be a positive integer';
        }

        if (! Course::whereKey((int) $data['course_id'])->exists()) {
            return "course_id '{$data['course_id']}' does not exist";
        }

        if (! empty($data['lesson_id'])) {
            if (! ctype_digit((string) $data['lesson_id'])) {
                return 'lesson_id must be a positive integer';
            }

            $lesson = Lesson::query()
                ->select('id', 'course_id')
                ->find((int) $data['lesson_id']);

            if ($lesson === null) {
                return "lesson_id '{$data['lesson_id']}' does not exist";
            }

            if ((int) $lesson->course_id !== (int) $data['course_id']) {
                return 'lesson_id does not belong to the given course_id';
            }
        }

        if (isset($data['pass_mark_percentage']) && $data['pass_mark_percentage'] !== '') {
            if (! ctype_digit((string) $data['pass_mark_percentage'])
                || (int) $data['pass_mark_percentage'] > 100) {
                return 'pass_mark_percentage must be a whole number from 0 to 100';
            }
        }

        if (isset($data['time_limit_minutes']) && $data['time_limit_minutes'] !== '') {
            if (! ctype_digit((string) $data['time_limit_minutes'])
                || (int) $data['time_limit_minutes'] < 1
                || (int) $data['time_limit_minutes'] > 1440) {
                return 'time_limit_minutes must be a whole number from 1 to 1440';
            }
        }

        if (isset($data['max_attempts']) && $data['max_attempts'] !== '') {
            if (! ctype_digit((string) $data['max_attempts'])
                || (int) $data['max_attempts'] < 1
                || (int) $data['max_attempts'] > 100) {
                return 'max_attempts must be a whole number from 1 to 100';
            }
        }

        return null;
    }

    /**
     * Build and save a quiz from a validated row.
     *
     * @param  array<string, mixed>  $data
     */
    private function createFromRow(array $data, int $actorId): Quiz
    {
        $attributes = $this->dataPreparation->prepareForCreation([
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'course_id' => (int) $data['course_id'],
            'lesson_id' => $this->nullableInt($data['lesson_id'] ?? null),
            'pass_mark_percentage' => $this->nullableInt($data['pass_mark_percentage'] ?? null),
            'time_limit_minutes' => $this->nullableInt($data['time_limit_minutes'] ?? null),
            'max_attempts' => $this->nullableInt($data['max_attempts'] ?? null),
        ], $actorId);

        $quiz = (new Quiz)->forceFill($attributes);
        $quiz->save();

        return $quiz;
    }

    /**
     * Cast a CSV value to a nullable integer.
     */
    private function nullableInt(?string $value): ?int
    {
        return $value === null || $value === '' ? null : (int) $value;
    }
}
