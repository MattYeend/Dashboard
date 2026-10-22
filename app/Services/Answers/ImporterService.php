<?php

declare(strict_types=1);

namespace App\Services\Answers;

use App\Models\Log;
use App\Models\Question;
use App\Models\User;
use App\Services\AuditLogService;
use App\Services\Concerns\ImportsViaPreview;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

/**
 * Handles CSV import of answers, including the preview/commit workflow
 * (via ImportsViaPreview) alongside the one-shot import() method.
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
        'question_id',
        'answer',
    ];

    /**
     * Inject the required services into the importer service.
     */
    public function __construct(
        protected readonly AuditLogService $auditLogService,
        protected readonly CreatorService $creator,
    ) {}

    /**
     * The private-disk directory this module's uploads are stored under.
     */
    protected function importStoragePath(): string
    {
        return 'imports/answers';
    }

    /**
     * The Log::ACTION_IMPORT_* constant for this module's batch log entry.
     */
    protected function importAuditAction(): int
    {
        return Log::ACTION_IMPORT_ANSWER;
    }

    /**
     * Persist a single validated row as a new Answer.
     *
     * Goes through CreatorService so the row lock, answer set rules and
     * audit logging apply exactly as they do for a form submission.
     *
     * @param  array<string, mixed>  $data
     * @param  array<string, mixed>  $context
     *
     * @throws AuthorizationException
     * @throws ValidationException
     */
    protected function persistRow(array $data, int $actorId, array $context = []): void
    {
        $actor = User::findOrFail($actorId);
        $question = Question::query()->findOrFail((int) $data['question_id']);

        Gate::forUser($actor)->authorize('update', $question);

        $position = trim((string) ($data['position'] ?? ''));

        $this->creator->create([
            'question_id' => $question->id,
            'answer' => $data['answer'],
            'is_correct' => $this->toBoolean($data['is_correct'] ?? null),
            'feedback' => ($data['feedback'] ?? '') !== '' ? $data['feedback'] : null,
            'position' => $position === '' ? null : (int) $position,
        ], $actorId);
    }

    /**
     * Import answers from an uploaded CSV file.
     *
     * @return array{imported: int, skipped: array<int, array{row: int, reason: string}>}
     */
    public function import(
        UploadedFile $file,
        int $actorId
    ): array {
        $handle = fopen($file->getRealPath(), 'r');

        try {
            $header = fgetcsv($handle);
            $header = array_map(fn (string $column) => strtolower(trim($column)), $header ?: []);

            $missing = array_diff(self::REQUIRED_COLUMNS, $header);

            if (! empty($missing)) {
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

            DB::transaction(function () use ($handle, $header, $actorId, &$imported, &$skipped, &$rowNumber) {
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

                    try {
                        $this->persistRow($data, $actorId);
                        $imported++;
                    } catch (ValidationException $exception) {
                        $skipped[] = [
                            'row' => $rowNumber,
                            'reason' => collect($exception->errors())->flatten()->first() ?? 'Invalid answer set',
                        ];
                    } catch (AuthorizationException) {
                        $skipped[] = [
                            'row' => $rowNumber,
                            'reason' => 'You cannot add answers to this question',
                        ];
                    }
                }
            });

            return ['imported' => $imported, 'skipped' => $skipped];
        } finally {
            fclose($handle);
        }
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

        if (! ctype_digit((string) $data['question_id'])
            || ! Question::query()->whereKey((int) $data['question_id'])->exists()) {
            return "question_id '{$data['question_id']}' does not exist";
        }

        if (mb_strlen((string) $data['answer']) > 2000) {
            return 'answer must not be longer than 2000 characters';
        }

        if (mb_strlen((string) ($data['feedback'] ?? '')) > 5000) {
            return 'feedback must not be longer than 5000 characters';
        }

        $position = trim((string) ($data['position'] ?? ''));

        if ($position !== '' && (! ctype_digit($position) || (int) $position > 65535)) {
            return 'position must be a whole number between 0 and 65535';
        }

        $correct = strtolower(trim((string) ($data['is_correct'] ?? '')));

        if ($correct !== '' && ! in_array($correct, ['1', '0', 'true', 'false', 'yes', 'no'], true)) {
            return "is_correct '{$data['is_correct']}' is not a valid yes or no value";
        }

        return null;
    }

    /**
     * Convert a CSV cell to a boolean.
     */
    private function toBoolean(mixed $value): bool
    {
        return in_array(strtolower(trim((string) $value)), ['1', 'true', 'yes'], true);
    }
}
