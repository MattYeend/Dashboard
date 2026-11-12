<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const LOG_TABLE = 'quote_status_backfill_log';

    /**
     * Seeded titles mapped to their system keys.
     *
     * @var array<string, string>
     */
    private const TITLE_TO_KEY = [
        'Draft' => 'draft',
        'Sent' => 'sent',
        'Accepted' => 'accepted',
        'Declined' => 'declined',
        'Expired' => 'expired',
        'Converted' => 'converted',
    ];

    public function up(): void
    {
        Schema::create(self::LOG_TABLE, function (Blueprint $table): void {
            $table->id();
            $table->string('action', 20);
            $table->unsignedBigInteger('subject_id');
            $table->unsignedBigInteger('original_id')->nullable();
            $table->unsignedBigInteger('keeper_id')->nullable();
            $table->json('payload')->nullable();
            $table->index('action');
        });

        DB::transaction(function (): void {
            foreach (self::TITLE_TO_KEY as $title => $key) {
                $byOrganisation = DB::table('quote_statuses')
                    ->whereNull('system_key')
                    ->where('title', $title)
                    ->orderBy('id')
                    ->get(['id', 'organisation_id'])
                    ->groupBy('organisation_id');

                foreach ($byOrganisation as $organisationId => $group) {
                    $organisationId = $organisationId === '' ? null : $organisationId;

                    $scoped = fn () => DB::table('quote_statuses')->when(
                        $organisationId === null,
                        fn ($q) => $q->whereNull('organisation_id'),
                        fn ($q) => $q->where('organisation_id', $organisationId),
                    );

                    $keeperId = $scoped()->where('system_key', $key)->value('id');

                    if ($keeperId === null) {
                        $keeperId = $group->first()->id;

                        DB::table('quote_statuses')
                            ->where('id', $keeperId)
                            ->update(['system_key' => $key]);

                        DB::table(self::LOG_TABLE)->insert([
                            'action' => 'key_set',
                            'subject_id' => $keeperId,
                        ]);
                    }

                    $duplicateIds = $group->pluck('id')
                        ->reject(fn ($id): bool => (int) $id === (int) $keeperId)
                        ->values()
                        ->all();

                    if ($duplicateIds === []) {
                        continue;
                    }

                    DB::table('quotes')
                        ->whereIn('status_id', $duplicateIds)
                        ->orderBy('id')
                        ->get(['id', 'status_id'])
                        ->chunk(500)
                        ->each(fn ($chunk) => DB::table(self::LOG_TABLE)->insert(
                            $chunk->map(fn ($quote): array => [
                                'action' => 'quote_repointed',
                                'subject_id' => $quote->id,
                                'original_id' => $quote->status_id,
                                'keeper_id' => $keeperId,
                            ])->all()
                        ));

                    DB::table('quotes')
                        ->whereIn('status_id', $duplicateIds)
                        ->update(['status_id' => $keeperId]);

                    DB::table('quote_statuses')
                        ->whereIn('id', $duplicateIds)
                        ->get()
                        ->each(fn ($row) => DB::table(self::LOG_TABLE)->insert([
                            'action' => 'row_deleted',
                            'subject_id' => $row->id,
                            'keeper_id' => $keeperId,
                            'payload' => json_encode($row, JSON_THROW_ON_ERROR),
                        ]));

                    DB::table('quote_statuses')
                        ->whereIn('id', $duplicateIds)
                        ->delete();
                }
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable(self::LOG_TABLE)) {
            return;
        }

        DB::transaction(function (): void {
            DB::table(self::LOG_TABLE)
                ->where('action', 'row_deleted')
                ->orderBy('id')
                ->get()
                ->each(function ($entry): void {
                    /** @var array<string, mixed> $row */
                    $row = json_decode($entry->payload, true, 512, JSON_THROW_ON_ERROR);

                    DB::table('quote_statuses')->insertOrIgnore($row);
                });

            DB::table(self::LOG_TABLE)
                ->where('action', 'quote_repointed')
                ->orderBy('id')
                ->get()
                ->each(fn ($entry) => DB::table('quotes')
                    ->where('id', $entry->subject_id)
                    ->where('status_id', $entry->keeper_id)
                    ->update(['status_id' => $entry->original_id]));

            DB::table('quote_statuses')
                ->whereIn(
                    'id',
                    DB::table(self::LOG_TABLE)->where('action', 'key_set')->pluck('subject_id')
                )
                ->update(['system_key' => null]);
        });

        Schema::dropIfExists(self::LOG_TABLE);
    }
};
