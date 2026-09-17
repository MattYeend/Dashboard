<?php

namespace App\Services\Concerns;

use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Adds a storeUpload/preview/commit workflow to a module's ImporterService.
 *
 * The implementing class supplies importStoragePath(), importAuditAction(),
 * validateRow() and persistRow(); this trait handles temporary storage,
 * ownership checks, re-validation at commit time, and the single
 * batch-level audit log entry.
 */
trait ImportsViaPreview
{
    /**
     * The disk imports are stored on while awaiting commit.
     */
    protected function importDisk(): string
    {
        return 'local';
    }

    /**
     * How long a stored upload remains valid before it must be re-uploaded.
     */
    protected function importCacheTtlMinutes(): int
    {
        return 5;
    }

    /**
     * The private-disk directory this module's uploads are stored under.
     */
    abstract protected function importStoragePath(): string;

    /**
     * The Log::ACTION_IMPORT_* constant for this module's batch log entry.
     */
    abstract protected function importAuditAction(): int;

    /**
     * Validate a single parsed row, returning an error string or null if valid.
     *
     * @param  array<string, mixed>  $data
     */
    abstract protected function validateRow(array $data): ?string;

    /**
     * Persist a single validated row. Must not write its own audit log —
     * the trait writes one summary entry for the whole batch.
     *
     * @param  array<string, mixed>  $data
     * @param  array<string, mixed>  $context  Extra identifiers a scoped import needs (e.g. a parent invoice or pipeline id).
     */
    abstract protected function persistRow(array $data, int $actorId, array $context = []): void;

    /**
     * Store an uploaded CSV privately and return a token identifying it
     * for the subsequent preview/commit steps.
     */
    public function storeUpload(UploadedFile $file, int $actorId, array $context = []): string
    {
        $token = (string) Str::uuid();

        $path = $file->storeAs($this->importStoragePath(), "{$token}.csv", $this->importDisk());

        Cache::put(
            $this->importCacheKey($token),
            ['actor_id' => $actorId, 'path' => $path, 'context' => $context],
            now()->addMinutes($this->importCacheTtlMinutes()),
        );

        return $token;
    }

    /**
     * Parse and validate the stored file for the given token, without
     * persisting anything.
     *
     * @return array{columns: array<int, string>, rows: array<int, array{row: int, data: array<string, mixed>, valid: bool, reason: ?string}>, valid_count: int, skipped_count: int}
     */
    public function preview(string $token, int $actorId): array
    {
        [$header, $rows] = $this->readStoredFile($token, $actorId);

        $preview = [];
        $validCount = 0;

        foreach ($rows as $rowNumber => $data) {
            $reason = $data === null
                ? 'Expected '.count($header).' columns'
                : $this->validateRow($data);

            $isValid = $reason === null && $data !== null;

            if ($isValid) {
                $validCount++;
            }

            $preview[] = [
                'row' => $rowNumber,
                'data' => $data ?? [],
                'valid' => $isValid,
                'reason' => $reason,
            ];
        }

        return [
            'columns' => $header,
            'rows' => $preview,
            'valid_count' => $validCount,
            'skipped_count' => count($preview) - $validCount,
        ];
    }

    /**
     * Re-validate and persist only the rows that still pass validation,
     * writing a single audit log entry summarising the batch.
     *
     * @return array{imported: int, skipped: array<int, array{row: int, reason: string}>}
     */
    public function commit(string $token, int $actorId): array
    {
        $context = Cache::get($this->importCacheKey($token))['context'] ?? [];

        [$header, $rows] = $this->readStoredFile($token, $actorId);

        $actor = User::findOrFail($actorId);
        $imported = 0;
        $skipped = [];

        DB::transaction(function () use ($rows, $header, $actorId, $context, &$imported, &$skipped) {
            foreach ($rows as $rowNumber => $data) {
                if ($data === null) {
                    $skipped[] = ['row' => $rowNumber, 'reason' => 'Expected '.count($header ?? []).' columns'];

                    continue;
                }

                $reason = $this->validateRow($data);

                if ($reason !== null) {
                    $skipped[] = ['row' => $rowNumber, 'reason' => $reason];

                    continue;
                }

                $this->persistRow($data, $actorId, $context);

                $imported++;
            }
        });

        if ($imported > 0) {
            app(AuditLogService::class)->record(
                $this->importAuditAction(),
                $actor,
                null,
                ['after' => ['imported' => $imported, 'skipped' => count($skipped)]],
            );
        }

        $this->cleanUp($token);

        return ['imported' => $imported, 'skipped' => $skipped];
    }

    /**
     * Discard a stored upload without committing it.
     */
    public function discardImport(string $token, int $actorId): void
    {
        $this->assertImportOwnership($token, $actorId);
        $this->cleanUp($token);
    }

    /**
     * Read and parse the stored file for a token, verifying ownership.
     *
     * @return array{0: array<int, string>, 1: array<int, ?array<string, mixed>>}
     */
    private function readStoredFile(string $token, int $actorId): array
    {
        $path = $this->assertImportOwnership($token, $actorId);

        $stream = Storage::disk($this->importDisk())->readStream($path);

        if ($stream === null) {
            throw new RuntimeException('Import file could not be read.');
        }

        $header = fgetcsv($stream);
        $header = array_map(fn (string $column) => strtolower(trim($column)), $header ?: []);

        $rows = [];
        $rowNumber = 1;

        while (($row = fgetcsv($stream)) !== false) {
            $rowNumber++;
            $rows[$rowNumber] = count($row) === count($header) ? array_combine($header, $row) : null;
        }

        fclose($stream);

        return [$header, $rows];
    }

    /**
     * Confirm the given token belongs to the given actor and return its path.
     */
    private function assertImportOwnership(string $token, int $actorId): string
    {
        $meta = Cache::get($this->importCacheKey($token));

        if (! $meta || $meta['actor_id'] !== $actorId) {
            throw new RuntimeException('Import session not found or expired. Please re-upload the file.');
        }

        return $meta['path'];
    }

    /**
     * Remove the stored file and cache entry for a token.
     */
    private function cleanUp(string $token): void
    {
        $meta = Cache::get($this->importCacheKey($token));

        if ($meta) {
            Storage::disk($this->importDisk())->delete($meta['path']);
        }

        Cache::forget($this->importCacheKey($token));
    }

    /**
     * Build the cache key for a token, namespaced per implementing class.
     */
    private function importCacheKey(string $token): string
    {
        return 'import:'.str_replace('\\', ':', static::class).':'.$token;
    }
}
