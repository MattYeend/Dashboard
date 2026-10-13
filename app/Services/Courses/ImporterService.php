<?php

declare(strict_types=1);

namespace App\Services\Courses;

use App\Models\Course;
use App\Models\Log;
use App\Models\User;
use App\Services\AuditLogService;
use App\Services\Concerns\ImportsViaPreview;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Handles CSV import of courses, including the preview/commit
 * workflow (via ImportsViaPreview) alongside the one-shot import() method.
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
        return 'imports/courses';
    }

    /**
     * The Log::ACTION_IMPORT_* constant for this module's batch log entry.
     */
    protected function importAuditAction(): int
    {
        return Log::ACTION_IMPORT_COURSE;
    }

    /**
     * Persist a single validated row as a new Course.
     *
     * @param  array<string, mixed>  $data
     * @param  array<string, mixed>  $context
     */
    protected function persistRow(array $data, int $actorId, array $context = []): void
    {
        $this->createCourse($data, User::findOrFail($actorId));
    }

    /**
     * Import courses from an uploaded CSV file.
     *
     * @return array{imported: int, skipped: array<int, array{row: int, reason: string}>}
     */
    public function import(
        UploadedFile $file,
        int $actorId
    ): array {
        $handle = fopen($file->getRealPath(), 'r');

        $header = fgetcsv($handle);
        $header = array_map(fn (?string $column) => strtolower(trim((string) $column)), $header ?: []);

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

        DB::transaction(function () use ($handle, $header, $actor, &$imported, &$skipped, &$rowNumber) {
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

                $course = $this->createCourse($data, $actor);

                $this->auditLogService->record(
                    Log::ACTION_IMPORT_COURSE,
                    $actor,
                    $course,
                    ['after' => $this->auditLogService->snapshot($course)],
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
            if (trim((string) ($data[$column] ?? '')) === '') {
                return "Missing value for '{$column}'";
            }
        }

        if (mb_strlen($data['title']) > 255) {
            return 'title must be 255 characters or fewer';
        }

        if (mb_strlen((string) ($data['summary'] ?? '')) > 500) {
            return 'summary must be 500 characters or fewer';
        }

        $difficulty = strtolower(trim((string) ($data['difficulty'] ?? '')));

        if ($difficulty !== '' && ! in_array($difficulty, FilterService::DIFFICULTIES, true)) {
            return 'difficulty must be one of: '.implode(', ', FilterService::DIFFICULTIES);
        }

        $duration = trim((string) ($data['estimated_duration_minutes'] ?? ''));

        if ($duration !== '' && (! ctype_digit($duration) || (int) $duration > 65535)) {
            return 'estimated_duration_minutes must be a whole number between 0 and 65535';
        }

        $publishedAt = trim((string) ($data['published_at'] ?? ''));

        if ($publishedAt !== '' && strtotime($publishedAt) === false) {
            return "published_at '{$publishedAt}' is not a valid date";
        }

        $instructorId = trim((string) ($data['instructor_id'] ?? ''));

        if ($instructorId !== '') {
            if (! ctype_digit($instructorId)) {
                return 'instructor_id must be a positive integer';
            }

            $exists = User::whereKey((int) $instructorId)
                ->where('organisation_id', Auth::user()?->organisation_id)
                ->exists();

            if (! $exists) {
                return "instructor_id '{$instructorId}' does not exist";
            }
        }

        return null;
    }

    /**
     * Build and save a course from a validated CSV row.
     *
     * @param  array<string, mixed>  $data
     */
    private function createCourse(array $data, User $actor): Course
    {
        $payload = $this->dataPreparation->prepareForCreation([
            'title' => trim($data['title']),
            'summary' => $this->nullableString($data['summary'] ?? null),
            'description' => $this->nullableString($data['description'] ?? null),
            'difficulty' => $this->nullableString(strtolower((string) ($data['difficulty'] ?? ''))) ?? 'beginner',
            'estimated_duration_minutes' => $this->nullableInt($data['estimated_duration_minutes'] ?? null),
            'instructor_id' => $this->nullableInt($data['instructor_id'] ?? null),
            'published_at' => $this->nullableString($data['published_at'] ?? null),
        ]);

        $course = new Course($payload);
        $course->forceFill([
            'organisation_id' => $actor->organisation_id,
            'created_by' => $actor->id,
        ])->save();

        return $course;
    }

    /**
     * Cast a CSV value to a nullable trimmed string.
     */
    private function nullableString(?string $value): ?string
    {
        $value = $value === null ? null : trim($value);

        return $value === null || $value === '' ? null : $value;
    }

    /**
     * Cast a CSV value to a nullable integer.
     */
    private function nullableInt(?string $value): ?int
    {
        return $value === null || trim($value) === '' ? null : (int) $value;
    }
}
