<?php

namespace App\Services\Labels;

use App\Models\Label;
use App\Models\Log;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ImporterService
{
    protected const REQUIRED_COLUMNS = [
        'name',
    ];

    /**
     * Inject the audit log service.
     */
    public function __construct(
        protected readonly AuditLogService $auditLogService,
    ) {}

    /**
     * Import labels from an uploaded CSV file.
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

                $slug = ! empty($data['slug']) ? $data['slug'] : Str::slug($data['name']);

                $label = Label::create([
                    'name' => $data['name'],
                    'slug' => $slug,
                    'background_colour' => $data['background_colour'] ?? '#6b7280',
                    'text_colour' => $data['text_colour'] ?? '#ffffff',
                    'created_by' => $actorId,
                ]);

                $this->auditLogService->record(
                    Log::ACTION_IMPORT_LABEL,
                    $actor,
                    $label,
                    ['after' => $this->auditLogService->snapshot($label)],
                );

                $imported++;
            }
        });

        fclose($handle);

        return ['imported' => $imported, 'skipped' => $skipped];
    }

    /**
     * Validate a single row, returning an error string or null if valid.
     */
    private function validateRow(array $data): ?string
    {
        foreach (self::REQUIRED_COLUMNS as $column) {
            if (empty($data[$column])) {
                return "Missing value for '{$column}'";
            }
        }

        $slug = ! empty($data['slug']) ? $data['slug'] : Str::slug($data['name']);

        if (Label::withTrashed()->where('slug', $slug)->exists()) {
            return "slug '{$slug}' already exists";
        }

        foreach (['background_colour', 'text_colour'] as $column) {
            if (! empty($data[$column]) && ! preg_match('/^#[0-9a-fA-F]{6}$/', trim($data[$column]))) {
                return "{$column} must be a valid hex code (e.g. #6b7280)";
            }
        }

        return null;
    }
}
