<?php

namespace App\Services\Tickets;

use App\Models\Label;
use App\Models\Log;
use App\Models\Ticket;
use App\Models\TicketPriority;
use App\Models\TicketStatus;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
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
     * Import tickets from an uploaded CSV file.
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

                $ticket = Ticket::create([
                    'title' => $data['title'],
                    'description' => Purifier::clean($data['description'], 'tickets'),
                    'ticket_status_id' => $this->resolveStatusId($data['ticket_status'] ?? null),
                    'ticket_priority_id' => $this->resolvePriorityId($data['ticket_priority'] ?? null),
                    'assigned_to' => $this->resolveAssigneeId($data['assigned_to'] ?? null),
                    'due_date' => $data['due_date'] ?? null,
                    'created_by' => $actorId,
                ]);

                if (! empty($data['labels'])) {
                    $ticket->labels()->sync($this->resolveLabelIds($data['labels']));
                }

                $this->auditLogService->record(
                    Log::ACTION_IMPORT_TICKET,
                    $actor,
                    $ticket,
                    ['after' => $this->auditLogService->snapshot($ticket)],
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
     * @param  array<string, string>  $data
     */
    private function validateRow(array $data): ?string
    {
        foreach (self::REQUIRED_COLUMNS as $column) {
            if (empty($data[$column])) {
                return "Missing value for '{$column}'";
            }
        }

        if (! empty($data['ticket_status']) && ! TicketStatus::where('title', $data['ticket_status'])->exists()) {
            return "ticket status '{$data['ticket_status']}' does not exist";
        }

        if (! empty($data['ticket_priority']) && ! TicketPriority::where('title', $data['ticket_priority'])->exists()) {
            return "ticket priority '{$data['ticket_priority']}' does not exist";
        }

        if (! empty($data['assigned_to']) && ! User::where('email', $data['assigned_to'])->exists()) {
            return "assignee '{$data['assigned_to']}' does not exist";
        }

        if (! empty($data['labels'])) {
            foreach ($this->splitList($data['labels']) as $name) {
                if (! Label::where('name', $name)->exists()) {
                    return "label '{$name}' does not exist";
                }
            }
        }

        return null;
    }

    /**
     * Resolve a ticket status title to its ID.
     */
    private function resolveStatusId(?string $value): ?int
    {
        if (empty($value)) {
            return null;
        }

        return TicketStatus::where('title', $value)->value('id');
    }

    /**
     * Resolve a ticket priority title to its ID.
     */
    private function resolvePriorityId(?string $value): ?int
    {
        if (empty($value)) {
            return null;
        }

        return TicketPriority::where('title', $value)->value('id');
    }

    /**
     * Resolve an assignee's email address to their user ID.
     */
    private function resolveAssigneeId(?string $value): ?int
    {
        if (empty($value)) {
            return null;
        }

        return User::where('email', $value)->value('id');
    }

    /**
     * Resolve a comma-separated list of label names to their IDs.
     *
     * @return array<int, int>
     */
    private function resolveLabelIds(string $value): array
    {
        return Label::whereIn('name', $this->splitList($value))
            ->pluck('id')
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
