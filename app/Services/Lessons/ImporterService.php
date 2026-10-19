<?php

declare(strict_types=1);

namespace App\Services\Lessons;

use App\Models\Course;
use App\Models\Lesson;
use App\Models\Log;
use App\Models\User;
use App\Services\AuditLogService;
use App\Services\Concerns\ChecksOrganisationBoundary;
use App\Services\Concerns\ImportsViaPreview;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

/**
 * Handles CSV import of lessons into a single course, including the
 * preview/commit workflow (via ImportsViaPreview) alongside the one-shot
 * import() method.
 */
class ImporterService
{
    use ChecksOrganisationBoundary;
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
     * Maximum number of data rows accepted in a single import.
     */
    protected const MAX_ROWS = 1000;

    /**
     * Accepted values for the is_preview column.
     *
     * @var array<int, string>
     */
    protected const BOOLEAN_VALUES = [
        '',
        '0',
        '1',
        'true',
        'false',
        'yes',
        'no',
    ];

    /**
     * Inject the required services into the importer service.
     */
    public function __construct(
        protected readonly DataPreparationService $dataPreparation,
        protected readonly VideoUrlService $videoUrlService,
        protected readonly AuditLogService $auditLogService,
    ) {}

    /**
     * The private-disk directory this module's uploads are stored under.
     */
    protected function importStoragePath(): string
    {
        return 'imports/lessons';
    }

    /**
     * The Log::ACTION_IMPORT_* constant for this module's batch log entry.
     */
    protected function importAuditAction(): int
    {
        return Log::ACTION_IMPORT_LESSON;
    }

    /**
     * Persist a single validated row as a new Lesson.
     *
     * The target course is supplied as $context['course_id'].
     *
     * @param  array<string, mixed>  $data
     * @param  array<string, mixed>  $context
     *
     * @throws AuthorizationException
     */
    protected function persistRow(array $data, int $actorId, array $context = []): void
    {
        $this->createLesson($data, $actorId, (int) ($context['course_id'] ?? 0));
    }

    /**
     * Import lessons from an uploaded CSV file into a course.
     *
     * @return array{imported: int, skipped: array<int, array{row: int, reason: string}>}
     *
     * @throws AuthorizationException
     */
    public function import(
        UploadedFile $file,
        int $actorId,
        int $courseId
    ): array {
        $this->assertCourseAccessible($courseId);

        $handle = fopen($file->getRealPath(), 'r');

        if ($handle === false) {
            return [
                'imported' => 0,
                'skipped' => [['row' => 0, 'reason' => 'The uploaded file could not be read']],
            ];
        }

        $header = fgetcsv($handle);
        $header = array_map(
            fn (string $column) => strtolower(trim(ltrim($column, "\xEF\xBB\xBF"))),
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

        DB::transaction(function () use ($handle, $header, $actor, $actorId, $courseId, &$imported, &$skipped, &$rowNumber) {
            while (($row = fgetcsv($handle)) !== false) {
                $rowNumber++;

                if ($rowNumber - 1 > self::MAX_ROWS) {
                    $skipped[] = [
                        'row' => $rowNumber,
                        'reason' => sprintf('Import limit of %d rows exceeded', self::MAX_ROWS),
                    ];

                    break;
                }

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

                $lesson = $this->createLesson($data, $actorId, $courseId);

                $this->auditLogService->record(
                    Log::ACTION_IMPORT_LESSON,
                    $actor,
                    $lesson,
                    ['after' => $this->auditLogService->snapshot($lesson)],
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

        if (mb_strlen(strip_tags((string) $data['title'])) > 255) {
            return "'title' must not exceed 255 characters";
        }

        if (mb_strlen(strip_tags((string) ($data['summary'] ?? ''))) > 500) {
            return "'summary' must not exceed 500 characters";
        }

        if (! $this->videoUrlService->isAllowed(trim((string) ($data['video_url'] ?? '')))) {
            return "'video_url' must be an https YouTube or Vimeo link";
        }

        $duration = trim((string) ($data['duration_minutes'] ?? ''));

        if ($duration !== '' && (! ctype_digit($duration) || (int) $duration < 1 || (int) $duration > 65535)) {
            return "'duration_minutes' must be a whole number between 1 and 65535";
        }

        $preview = strtolower(trim((string) ($data['is_preview'] ?? '')));

        if (! in_array($preview, self::BOOLEAN_VALUES, true)) {
            return "'is_preview' must be one of: 1, 0, true, false, yes, no";
        }

        return null;
    }

    /**
     * Create a draft lesson from a validated row.
     *
     * @param  array<string, mixed>  $data
     *
     * @throws AuthorizationException
     */
    protected function createLesson(array $data, int $actorId, int $courseId): Lesson
    {
        $this->assertCourseAccessible($courseId);

        return DB::transaction(function () use ($data, $actorId, $courseId): Lesson {
            $prepared = $this->dataPreparation->prepareForCreation([
                'course_id' => $courseId,
                'title' => (string) $data['title'],
                'summary' => $data['summary'] ?? null,
                'content' => $data['content'] ?? null,
                'video_url' => $data['video_url'] ?? null,
                'duration_minutes' => ($data['duration_minutes'] ?? '') === ''
                    ? null
                    : (int) $data['duration_minutes'],
                'is_preview' => in_array(
                    strtolower(trim((string) ($data['is_preview'] ?? ''))),
                    ['1', 'true', 'yes'],
                    true
                ),
                'published_at' => null,
            ]);

            $lesson = new Lesson;
            $lesson->forceFill(array_merge($prepared, ['created_by' => $actorId]))->save();

            return $lesson;
        });
    }

    /**
     * Ensure the target course exists and belongs to the current organisation.
     *
     * @throws AuthorizationException
     */
    protected function assertCourseAccessible(int $courseId): void
    {
        $course = Course::query()->findOrFail($courseId);

        if (! $this->belongsToCurrentOrganisation($course)) {
            throw new AuthorizationException('The selected course is not available.');
        }
    }
}
