<?php

declare(strict_types=1);

namespace App\Services\EmailSuppressions;

use App\Models\EmailSuppression;
use App\Models\Log;
use App\Models\User;
use App\Services\AuditLogService;
use App\Services\Concerns\ImportsViaPreview;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

/**
 * Handles CSV import of email suppressions, including the preview/commit
 * workflow (via ImportsViaPreview) alongside the one-shot import() method.
 *
 * Campaign and contact are not importable, so no foreign keys cross a
 * tenant boundary. Rows that already exist in the tenant (including
 * soft-deleted rows) are skipped, so the unique index is never tripped.
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
        'email',
    ];

    /**
     * Column headers that may be present in the uploaded CSV.
     *
     * @var array<int, string>
     */
    protected const OPTIONAL_COLUMNS = [
        'reason',
        'notes',
    ];

    /**
     * Maximum length of the varchar columns.
     */
    protected const MAX_STRING_LENGTH = 255;

    /**
     * Maximum length of the notes column.
     */
    protected const MAX_NOTES_LENGTH = 2000;

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
        return 'imports/email_suppressions';
    }

    /**
     * The Log::ACTION_IMPORT_* constant for this module's batch log entry.
     */
    protected function importAuditAction(): int
    {
        return Log::ACTION_IMPORT_EMAIL_SUPPRESSION;
    }

    /**
     * Persist a single validated row as a new EmailSuppression.
     *
     * @param  array<string, mixed>  $data
     * @param  array<string, mixed>  $context
     */
    protected function persistRow(array $data, int $actorId, array $context = []): void
    {
        $this->createFromRow($data, $actorId);
    }

    /**
     * Import email suppressions from an uploaded CSV file.
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
            fn (string $column) => strtolower(trim(str_replace("\xEF\xBB\xBF", '', $column))),
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

        DB::transaction(function () use ($handle, $header, $actor, $actorId, &$imported, &$skipped, &$rowNumber): void {
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

                $emailSuppression = $this->createFromRow($data, $actorId);

                $this->auditLogService->record(
                    Log::ACTION_IMPORT_EMAIL_SUPPRESSION,
                    $actor,
                    $emailSuppression,
                    ['after' => $this->auditLogService->snapshot($emailSuppression)],
                );

                $imported++;
            }
        });

        fclose($handle);

        return ['imported' => $imported, 'skipped' => $skipped];
    }

    /**
     * Validate a single row, returning a human readable error or null if valid.
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

        foreach (['email', 'reason'] as $column) {
            $value = isset($data[$column]) ? (string) $data[$column] : '';

            if (mb_strlen($value) > self::MAX_STRING_LENGTH) {
                return "'{$column}' must not exceed ".self::MAX_STRING_LENGTH.' characters';
            }

            if (preg_match('/[\x00-\x1F\x7F]/u', $value) === 1) {
                return "'{$column}' must not contain line breaks or control characters";
            }
        }

        $email = mb_strtolower(trim((string) $data['email']));

        if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            return "'email' must be a valid email address";
        }

        $reason = mb_strtolower(trim((string) ($data['reason'] ?? '')));

        if ($reason !== '' && ! in_array($reason, EmailSuppression::REASONS, true)) {
            return "'reason' must be one of: ".implode(', ', EmailSuppression::REASONS);
        }

        if (mb_strlen((string) ($data['notes'] ?? '')) > self::MAX_NOTES_LENGTH) {
            return "'notes' must not exceed ".self::MAX_NOTES_LENGTH.' characters';
        }

        $existing = EmailSuppression::withTrashed()
            ->where('email', $email)
            ->first();

        if ($existing !== null) {
            return $existing->trashed()
                ? "'email' is already on the list but deleted, restore it instead"
                : "'email' is already on the suppression list";
        }

        return null;
    }

    /**
     * Trim values, convert empty strings to null and map the CSV row to
     * the attributes expected by DataPreparationService.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function attributesFromRow(array $data): array
    {
        $clean = static function (mixed $value): ?string {
            if ($value === null) {
                return null;
            }

            $trimmed = trim((string) $value);

            return $trimmed === '' ? null : $trimmed;
        };

        return [
            'email' => $clean($data['email'] ?? null),
            'reason' => $clean($data['reason'] ?? null),
            'notes' => $clean($data['notes'] ?? null),
        ];
    }

    /**
     * Create an email suppression from a validated row and stamp the creator.
     *
     * Returns the existing row untouched if the address is already present,
     * as a guard for the preview/commit path where rows are validated before
     * earlier rows in the same file are persisted.
     *
     * @param  array<string, mixed>  $data
     */
    protected function createFromRow(array $data, int $actorId): EmailSuppression
    {
        $attributes = $this->dataPreparation->prepareForCreation(
            $this->attributesFromRow($data)
        );

        $existing = EmailSuppression::withTrashed()
            ->where('email', $attributes['email'])
            ->first();

        if ($existing !== null) {
            return $existing;
        }

        $emailSuppression = EmailSuppression::create($attributes);

        $emailSuppression->forceFill(['created_by' => $actorId])->save();

        return $emailSuppression;
    }
}
