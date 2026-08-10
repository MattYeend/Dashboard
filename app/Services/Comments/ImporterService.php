<?php

namespace App\Services\Comments;

use App\Models\Comment;
use App\Models\Log;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class ImporterService
{
    protected const REQUIRED_COLUMNS = ['commentable_type', 'commentable_id', 'content'];

    public function __construct(
        protected readonly AuditLogService $auditLogService,
        protected readonly CommentableTypeRegistryService $registry,
    ) {}

    /**
     * @return array{imported: int, skipped: array<int, array{row: int, reason: string}>}
     */
    public function import(UploadedFile $file, int $actorId): array
    {
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
                        'reason' => sprintf('Expected %d columns but found %d', count($header), count($row)),
                    ];

                    continue;
                }

                $data = array_combine($header, $row);

                if (empty($data['content'])) {
                    $skipped[] = ['row' => $rowNumber, 'reason' => "Missing value for 'content'"];

                    continue;
                }

                if (mb_strlen($data['content']) > 10000) {
                    $skipped[] = ['row' => $rowNumber, 'reason' => "'content' exceeds 10,000 characters"];

                    continue;
                }

                $modelClass = $this->registry->modelClassForKey($data['commentable_type'] ?? '');

                if (! $modelClass) {
                    $skipped[] = ['row' => $rowNumber, 'reason' => "Unrecognised 'commentable_type'"];

                    continue;
                }

                if (empty($data['commentable_id']) || ! $modelClass::query()->whereKey($data['commentable_id'])->exists()) {
                    $skipped[] = ['row' => $rowNumber, 'reason' => "'commentable_id' does not exist for the given type"];

                    continue;
                }

                $comment = Comment::create([
                    'commentable_type' => $modelClass,
                    'commentable_id' => $data['commentable_id'],
                    'content' => $data['content'],
                    'created_by' => $actorId,
                ]);

                $this->auditLogService->record(
                    Log::ACTION_IMPORT_COMMENT,
                    $actor,
                    $comment,
                    ['after' => $this->auditLogService->snapshot($comment)],
                );

                $imported++;
            }
        });

        fclose($handle);

        return ['imported' => $imported, 'skipped' => $skipped];
    }
}
