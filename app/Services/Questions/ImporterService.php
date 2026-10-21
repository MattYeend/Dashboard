<?php

declare(strict_types=1);

namespace App\Services\Questions;

use App\Actions\Questions\ResolveQuestionableAction;
use App\Enums\QuestionType;
use App\Models\Log;
use App\Models\Question;
use App\Models\User;
use App\Services\AuditLogService;
use App\Services\Concerns\ImportsViaPreview;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

/**
 * Handles CSV import of questions, including the preview/commit
 * workflow (via ImportsViaPreview) alongside the one-shot
 * import() method.
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
        'questionable_type',
        'questionable_id',
        'question',
        'type',
        'points',
    ];

    /**
     * Inject the required services into the importer service.
     */
    public function __construct(
        protected readonly DataPreparationService $dataPreparation,
        protected readonly ResolveQuestionableAction $resolveQuestionable,
        protected readonly AuditLogService $auditLogService,
    ) {}

    /**
     * The private-disk directory this module's uploads are stored under.
     */
    protected function importStoragePath(): string
    {
        return 'imports/questions';
    }

    /**
     * The Log::ACTION_IMPORT_* constant for this module's batch log entry.
     */
    protected function importAuditAction(): int
    {
        return Log::ACTION_IMPORT_QUESTION;
    }

    /**
     * Persist a single validated row as a new Question.
     *
     * @param  array<string, mixed>  $data
     * @param  array<string, mixed>  $context
     */
    protected function persistRow(array $data, int $actorId, array $context = []): void
    {
        $this->createQuestion($data, $actorId);
    }

    /**
     * Import questions from an uploaded CSV file.
     *
     * @return array{imported: int, skipped: array<int, array{row: int, reason: string}>}
     */
    public function import(
        UploadedFile $file,
        int $actorId
    ): array {
        $handle = fopen($file->getRealPath(), 'r');

        $header = fgetcsv($handle);
        $header = array_map(fn (string $column) => strtolower(trim($column)), $header ?: []);

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

                $question = $this->createQuestion($data, $actorId);

                $this->auditLogService->record(
                    Log::ACTION_IMPORT_QUESTION,
                    $actor,
                    $question,
                    ['after' => $this->auditLogService->snapshot($question)],
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

        $alias = strtolower(trim($data['questionable_type']));

        if (! array_key_exists($alias, config('questions.questionable_types', []))) {
            return "'{$data['questionable_type']}' is not a permitted questionable type";
        }

        if (! ctype_digit((string) $data['questionable_id'])) {
            return 'questionable_id must be a positive integer';
        }

        if (($this->resolveQuestionable)($alias, $data['questionable_id']) === null) {
            return "No {$alias} found with id '{$data['questionable_id']}'";
        }

        if (mb_strlen((string) $data['question']) > 2000) {
            return 'question may not exceed 2000 characters';
        }

        if (QuestionType::tryFrom(strtolower(trim($data['type']))) === null) {
            return "'{$data['type']}' is not a valid question type";
        }

        if (! ctype_digit((string) $data['points']) || (int) $data['points'] > 1000) {
            return 'points must be a whole number between 0 and 1000';
        }

        if (isset($data['position']) && $data['position'] !== ''
            && (! ctype_digit((string) $data['position']) || (int) $data['position'] > 65535)) {
            return 'position must be a whole number between 0 and 65535';
        }

        return null;
    }

    /**
     * Create a question from a validated CSV row.
     *
     * @param  array<string, mixed>  $data
     */
    private function createQuestion(array $data, int $actorId): Question
    {
        $payload = $this->dataPreparation->prepareForCreation([
            'questionable_type' => strtolower(trim($data['questionable_type'])),
            'questionable_id' => (int) $data['questionable_id'],
            'question' => $data['question'],
            'type' => strtolower(trim($data['type'])),
            'explanation' => ($data['explanation'] ?? '') !== '' ? $data['explanation'] : null,
            'points' => (int) $data['points'],
            'position' => ($data['position'] ?? '') !== '' ? (int) $data['position'] : null,
            'is_required' => ($data['is_required'] ?? '') === ''
                ? true
                : filter_var($data['is_required'], FILTER_VALIDATE_BOOLEAN),
        ]);

        $question = Question::create($payload);

        $question->forceFill(['created_by' => $actorId])->save();

        return $question;
    }
}
