<?php

namespace App\Services\Categories;

use App\Models\Category;
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
     * Import categories from an uploaded CSV file.
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

                $category = Category::create([
                    'name' => $data['name'],
                    'slug' => ! empty($data['slug']) ? $data['slug'] : Str::slug($data['name']),
                    'description' => $data['description'] ?? null,
                    'parent_id' => ! empty($data['parent_id']) ? (int) $data['parent_id'] : null,
                    'created_by' => $actorId,
                ]);

                $this->auditLogService->record(
                    Log::ACTION_IMPORT_CATEGORY,
                    $actor,
                    $category,
                    ['after' => $this->auditLogService->snapshot($category)],
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

        if (mb_strlen($data['name']) > 255) {
            return "'name' exceeds 255 characters";
        }

        $slug = ! empty($data['slug']) ? $data['slug'] : Str::slug($data['name']);

        if (Category::withTrashed()->where('slug', $slug)->exists()) {
            return "Slug '{$slug}' already exists";
        }

        if (! empty($data['parent_id'])) {
            if (! ctype_digit((string) $data['parent_id'])) {
                return "'parent_id' must be a positive integer";
            }

            if (! Category::query()->whereKey((int) $data['parent_id'])->exists()) {
                return "'parent_id' {$data['parent_id']} does not exist";
            }
        }

        return null;
    }
}
