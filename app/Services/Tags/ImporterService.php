<?php

namespace App\Services\Tags;

use App\Models\Log;
use App\Models\Tag;
use App\Models\User;
use App\Services\AuditLogService;
use App\Services\SlugService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class ImporterService
{
    protected const REQUIRED_COLUMNS = [
        'name',
    ];

    /**
     * Inject the required services into the importer service.
     */
    public function __construct(
        protected readonly SlugService $slugService,
        protected readonly AuditLogService $auditLogService,
    ) {}

    /**
     * Import tags from an uploaded CSV file.
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

                $data = array_combine($header, $row);

                $error = $this->validateRow($data);

                if ($error !== null) {
                    $skipped[] = ['row' => $rowNumber, 'reason' => $error];

                    continue;
                }

                $name = strip_tags($data['name']);

                $tag = Tag::create([
                    'name' => $name,
                    'slug' => ! empty($data['slug'])
                        ? $data['slug']
                        : $this->slugService->generateUnique(Tag::class, $name),
                    'created_by' => $actorId,
                ]);

                $this->auditLogService->record(
                    Log::ACTION_IMPORT_TAG,
                    $actor,
                    $tag,
                    ['after' => $this->auditLogService->snapshot($tag)],
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

        if (! empty($data['slug']) && Tag::withTrashed()->where('slug', $data['slug'])->exists()) {
            return "slug '{$data['slug']}' already exists";
        }

        return null;
    }
}
