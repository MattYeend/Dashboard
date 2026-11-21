<?php

declare(strict_types=1);

namespace App\Services\EmailTemplates;

use App\Models\EmailTemplate;
use App\Models\Log;
use App\Models\User;
use App\Services\AuditLogService;
use App\Services\Concerns\ImportsViaPreview;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

/**
 * Handles CSV import of email templates, including the preview/commit
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
        'name',
        'subject',
        'body_html',
    ];

    /**
     * Column headers that may be present in the uploaded CSV.
     *
     * @var array<int, string>
     */
    protected const OPTIONAL_COLUMNS = [
        'preheader',
        'body_text',
        'is_active',
    ];

    /**
     * Maximum length of the varchar columns (name, subject, preheader).
     */
    protected const MAX_STRING_LENGTH = 255;

    /**
     * Maximum length of the TEXT column (body_text).
     */
    protected const MAX_TEXT_LENGTH = 65535;

    /**
     * Accepted spellings of a boolean value.
     *
     * @var array<int, string>
     */
    protected const TRUE_VALUES = ['1', 'true', 'yes', 'y'];

    /**
     * @var array<int, string>
     */
    protected const FALSE_VALUES = ['0', 'false', 'no', 'n'];

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
        return 'imports/email_templates';
    }

    /**
     * The Log::ACTION_IMPORT_* constant for this module's batch log entry.
     */
    protected function importAuditAction(): int
    {
        return Log::ACTION_IMPORT_EMAIL_TEMPLATE;
    }

    /**
     * Persist a single validated row as a new EmailTemplate.
     *
     * @param  array<string, mixed>  $data
     * @param  array<string, mixed>  $context
     */
    protected function persistRow(array $data, int $actorId, array $context = []): void
    {
        $this->createFromRow($data, $actorId);
    }

    /**
     * Import email templates from an uploaded CSV file.
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

                $emailTemplate = $this->createFromRow($data, $actorId);

                $this->auditLogService->record(
                    Log::ACTION_IMPORT_EMAIL_TEMPLATE,
                    $actor,
                    $emailTemplate,
                    ['after' => $this->auditLogService->snapshot($emailTemplate)],
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

        foreach (['name', 'subject', 'preheader'] as $column) {
            if (isset($data[$column]) && mb_strlen((string) $data[$column]) > self::MAX_STRING_LENGTH) {
                return "'{$column}' must not exceed ".self::MAX_STRING_LENGTH.' characters';
            }
        }

        if (isset($data['body_text']) && mb_strlen((string) $data['body_text']) > self::MAX_TEXT_LENGTH) {
            return "'body_text' must not exceed ".self::MAX_TEXT_LENGTH.' characters';
        }

        if (isset($data['is_active']) && trim((string) $data['is_active']) !== '') {
            $value = strtolower(trim((string) $data['is_active']));

            if (! in_array($value, [...self::TRUE_VALUES, ...self::FALSE_VALUES], true)) {
                return "'is_active' must be one of: 1, 0, true, false, yes, no";
            }
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

        $isActive = $clean($data['is_active'] ?? null);

        return [
            'name' => $clean($data['name'] ?? null),
            'subject' => $clean($data['subject'] ?? null),
            'preheader' => $clean($data['preheader'] ?? null),
            'body_html' => $clean($data['body_html'] ?? null),
            'body_text' => $clean($data['body_text'] ?? null),
            'is_active' => $isActive === null
                ? true
                : in_array(strtolower($isActive), self::TRUE_VALUES, true),
        ];
    }

    /**
     * Create an email template from a validated row. body_html passes
     * through the HTML purifier via DataPreparationService.
     *
     * @param  array<string, mixed>  $data
     */
    protected function createFromRow(array $data, int $actorId): EmailTemplate
    {
        $attributes = $this->dataPreparation->prepareForCreation(
            $this->attributesFromRow($data)
        );

        $emailTemplate = EmailTemplate::create($attributes);

        $emailTemplate->forceFill(['created_by' => $actorId])->save();

        return $emailTemplate;
    }
}
