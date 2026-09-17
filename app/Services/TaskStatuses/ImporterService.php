<?php

namespace App\Services\TaskStatuses;

use App\Models\Log;
use App\Models\TaskStatus;
use App\Models\User;
use App\Services\AuditLogService;
use App\Services\Concerns\ImportsViaPreview;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

/**
 * Handles CSV import of task statuses, including the newer preview/commit
 * workflow (via ImportsViaPreview) alongside the original one-shot
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
        'title',
    ];

    /**
     * Accepted hex colour formats (3 or 6 hex digits).
     */
    protected const HEX_COLOUR_PATTERN = '/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/';

    /**
     * Inject the required services into the importer service.
     */
    public function __construct(
        protected readonly AuditLogService $auditLogService,
    ) {}

    /**
     * The private-disk directory this module's uploads are stored under.
     */
    protected function importStoragePath(): string
    {
        return 'imports/task-statuses';
    }

    /**
     * The Log::ACTION_IMPORT_* constant for this module's batch log entry.
     */
    protected function importAuditAction(): int
    {
        return Log::ACTION_IMPORT_TASK_STATUS;
    }

    /**
     * Persist a single validated row as a new TaskStatus.
     *
     * @param  array<string, mixed>  $data
     * @param  array<string, mixed>  $context
     */
    protected function persistRow(array $data, int $actorId, array $context = []): void
    {
        TaskStatus::create([
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'background_colour' => $data['background_colour'] ?? '#ffffff',
            'text_colour' => $data['text_colour'] ?? '#000000',
            'created_by' => $actorId,
        ]);
    }

    /**
     * Import task statuses from an uploaded CSV file.
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

                $taskStatus = TaskStatus::create([
                    'title' => $data['title'],
                    'description' => $data['description'] ?? null,
                    'background_colour' => $data['background_colour'] ?? '#ffffff',
                    'text_colour' => $data['text_colour'] ?? '#000000',
                    'created_by' => $actorId,
                ]);

                $this->auditLogService->record(
                    Log::ACTION_IMPORT_TASK_STATUS,
                    $actor,
                    $taskStatus,
                    ['after' => $this->auditLogService->snapshot($taskStatus)],
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
            if (empty($data[$column])) {
                return "Missing value for '{$column}'";
            }
        }

        if (! empty($data['background_colour']) && ! preg_match(self::HEX_COLOUR_PATTERN, $data['background_colour'])) {
            return "'{$data['background_colour']}' is not a valid hex colour for 'background_colour'";
        }

        if (! empty($data['text_colour']) && ! preg_match(self::HEX_COLOUR_PATTERN, $data['text_colour'])) {
            return "'{$data['text_colour']}' is not a valid hex colour for 'text_colour'";
        }

        return null;
    }
}
