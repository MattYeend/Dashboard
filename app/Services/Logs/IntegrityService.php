<?php

namespace App\Services\Logs;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class IntegrityService
{
    /**
     * Seal every unsealed row into the hash chain, in id order, and return
     * how many rows were sealed.
     */
    public function seal(): int
    {
        $key = $this->key();
        $chunk = (int) config('audit.seal_chunk_size', 500);
        $total = 0;

        do {
            $count = DB::transaction(function () use ($key, $chunk): int {
                $last = DB::table('logs')
                    ->whereNotNull('sequence')
                    ->orderByDesc('sequence')
                    ->lockForUpdate()
                    ->first(['sequence', 'hash']);

                $sequence = (int) ($last->sequence ?? 0);
                $previousHash = $last->hash ?? null;

                $rows = DB::table('logs')
                    ->whereNull('sequence')
                    ->orderBy('id')
                    ->limit($chunk)
                    ->lockForUpdate()
                    ->get();

                foreach ($rows as $row) {
                    $sequence++;
                    $hash = $this->hash($row, $previousHash, $key);

                    DB::table('logs')->where('id', $row->id)->update([
                        'sequence' => $sequence,
                        'previous_hash' => $previousHash,
                        'hash' => $hash,
                        'sealed_at' => now(),
                    ]);

                    $previousHash = $hash;
                }

                return $rows->count();
            });

            $total += $count;
        } while ($count === $chunk);

        return $total;
    }

    /**
     * Verify the chain and return a list of failures. An empty list means intact.
     *
     * @return array<int, array{sequence: int, reason: string}>
     */
    public function verify(?int $fromSequence = null): array
    {
        $key = $this->key();
        $failures = [];
        $previous = null;

        DB::table('logs')
            ->whereNotNull('sequence')
            ->when($fromSequence !== null, fn ($query) => $query->where('sequence', '>=', $fromSequence))
            ->orderBy('sequence')
            ->chunkById((int) config('audit.verify_chunk_size', 1000), function ($rows) use ($key, &$failures, &$previous): void {
                foreach ($rows as $row) {
                    if ($previous !== null) {
                        if ((int) $row->sequence !== $previous['sequence'] + 1) {
                            $failures[] = ['sequence' => (int) $row->sequence, 'reason' => 'gap in sequence (row removed)'];
                        } elseif ($row->previous_hash !== $previous['hash']) {
                            $failures[] = ['sequence' => (int) $row->sequence, 'reason' => 'previous hash does not match'];
                        }
                    }

                    if (! hash_equals((string) $row->hash, $this->hash($row, $row->previous_hash, $key))) {
                        $failures[] = ['sequence' => (int) $row->sequence, 'reason' => 'row content does not match its hash'];
                    }

                    $previous = ['sequence' => (int) $row->sequence, 'hash' => $row->hash];
                }
            }, 'sequence');

        return $failures;
    }

    /**
     * Count rows that have waited too long to be sealed.
     */
    public function unsealedOlderThan(int $minutes): int
    {
        return DB::table('logs')
            ->whereNull('sequence')
            ->where('created_at', '<', now()->subMinutes($minutes))
            ->count();
    }

    /**
     * Compute the keyed hash for a row.
     */
    public function hash(object $row, ?string $previousHash, string $key): string
    {
        $payload = json_encode([
            'id' => (int) $row->id,
            'action_id' => (int) $row->action_id,
            'data' => $this->canonical($this->decode($row->data)),
            'logged_in_user_id' => $row->logged_in_user_id === null ? null : (int) $row->logged_in_user_id,
            'related_to_user_id' => $row->related_to_user_id === null ? null : (int) $row->related_to_user_id,
            'created_at' => Carbon::parse($row->created_at)->format('Y-m-d H:i:s'),
            'previous_hash' => $previousHash,
        ], JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRESERVE_ZERO_FRACTION);

        return hash_hmac('sha256', $payload, $key);
    }

    /**
     * Decode a stored JSON value.
     */
    private function decode(mixed $value): mixed
    {
        return is_string($value) ? json_decode($value, true) : $value;
    }

    /**
     * Sort associative keys recursively, since MySQL does not preserve JSON
     * key order.
     */
    private function canonical(mixed $value): mixed
    {
        if (! is_array($value)) {
            return $value;
        }

        $value = array_map(fn (mixed $item): mixed => $this->canonical($item), $value);

        if (! array_is_list($value)) {
            ksort($value);
        }

        return $value;
    }

    /**
     * Read the integrity key, refusing to run without a strong one.
     */
    private function key(): string
    {
        $key = (string) config('audit.hmac_key');

        if (strlen($key) < 32) {
            throw new RuntimeException('AUDIT_LOG_HMAC_KEY must be set to at least 32 characters.');
        }

        return $key;
    }
}
