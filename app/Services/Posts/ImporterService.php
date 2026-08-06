<?php

namespace App\Services\Posts;

use App\Models\Category;
use App\Models\Log;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Mews\Purifier\Facades\Purifier;

class ImporterService
{
    protected const REQUIRED_COLUMNS = [
        'title',
        'description',
    ];

    /**
     * Inject the audit log service.
     */
    public function __construct(
        protected readonly AuditLogService $auditLogService,
    ) {}

    /**
     * Import posts from an uploaded CSV file.
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

                $post = Post::create([
                    'title' => $data['title'],
                    'description' => Purifier::clean($data['description'], 'posts'),
                    'image' => $data['image'] ?? null,
                    'created_by' => $actorId,
                ]);

                if (! empty($data['categories'])) {
                    $post->categories()->sync($this->resolveCategoryIds($data['categories']));
                }

                if (! empty($data['tags'])) {
                    $post->tags()->sync($this->resolveTagIds($data['tags']));
                }

                $this->auditLogService->record(
                    Log::ACTION_IMPORT_POST,
                    $actor,
                    $post,
                    ['after' => $this->auditLogService->snapshot($post)],
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

        if (! empty($data['categories'])) {
            foreach ($this->splitList($data['categories']) as $name) {
                if (! Category::where('name', $name)->exists()) {
                    return "category '{$name}' does not exist";
                }
            }
        }

        return null;
    }

    /**
     * Resolve a comma-separated list of category names to their IDs.
     *
     * @return array<int, int>
     */
    private function resolveCategoryIds(string $value): array
    {
        return Category::whereIn('name', $this->splitList($value))
            ->pluck('id')
            ->all();
    }

    /**
     * Resolve a comma-separated list of tag names to their IDs, creating
     * any tags that don't yet exist.
     *
     * @return array<int, int>
     */
    private function resolveTagIds(string $value): array
    {
        return collect($this->splitList($value))
            ->map(fn (string $name) => Tag::firstOrCreate(
                ['name' => $name],
                ['slug' => Str::slug($name)]
            )->id)
            ->all();
    }

    /**
     * Split a comma-separated CSV cell into a clean list of values.
     *
     * @return array<int, string>
     */
    private function splitList(string $value): array
    {
        return collect(explode(',', $value))
            ->map(fn (string $item) => trim($item))
            ->filter()
            ->values()
            ->all();
    }
}
