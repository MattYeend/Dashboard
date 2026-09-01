<?php

namespace App\Services\Logs;

use App\Models\Log;
use App\Models\User;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ManagementService
{
    /**
     * Inject the required services into the management service.
     */
    public function __construct(
        protected readonly DeleterService $destructor,
        protected readonly ExporterService $exporter,
    ) {}

    /**
     * Permanently delete an activity log entry.
     */
    public function destroy(Log $log, User $actor): void
    {
        $this->destructor->delete($log, $actor->id, $actor);
    }

    /**
     * Bulk permanently delete activity log entries.
     */
    public function bulkDelete(array $ids, User $actor, callable $authoriseCallback): array
    {
        $requestedIds = collect($ids)->unique()->values();

        $logs = Log::whereIn('id', $requestedIds)->get();

        $deleted = [];

        foreach ($logs as $log) {
            /** @var Log $log */
            $authoriseCallback($log);
            $this->destructor->delete($log, $actor->id, $actor);
            $deleted[] = $log->id;
        }

        return [
            'deleted' => $deleted,
            'skipped' => $requestedIds->diff($logs->pluck('id'))->values()->all(),
        ];
    }

    /**
     * Export activity logs matching the given filters as a CSV download.
     *
     * @param  array<string, mixed>  $filters
     */
    public function export(array $filters): StreamedResponse
    {
        return $this->exporter->export($filters);
    }
}
