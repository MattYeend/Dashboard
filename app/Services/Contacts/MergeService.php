<?php

namespace App\Services\Contacts;

use App\Models\Address;
use App\Models\Contact;
use App\Models\InteractionLog;
use App\Models\Log;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class MergeService
{
    /**
     * Inject the required services into the service.
     */
    public function __construct(
        protected readonly ManagementService $management,
        protected readonly AuditLogService $auditLog,
    ) {}

    /**
     * Merge the duplicate contact into the survivor, reassigning
     * addresses and interaction logs, then soft-deleting the
     * duplicate.
     */
    public function merge(Contact $survivor, Contact $duplicate, User $actor): Contact
    {
        if ($survivor->is($duplicate)) {
            throw new InvalidArgumentException('A contact cannot be merged into itself.');
        }

        $before = [
            'survivor' => $survivor->auditSnapshot(),
            'duplicate' => $duplicate->auditSnapshot(),
        ];

        DB::transaction(function () use ($survivor, $duplicate, $actor): void {
            Address::where('addressable_type', $duplicate->getMorphClass())
                ->where('addressable_id', $duplicate->id)
                ->update([
                    'addressable_id' => $survivor->id,
                    'addressable_type' => $survivor->getMorphClass(),
                ]);

            InteractionLog::where('interactable_type', $duplicate->getMorphClass())
                ->where('interactable_id', $duplicate->id)
                ->update([
                    'interactable_id' => $survivor->id,
                    'interactable_type' => $survivor->getMorphClass(),
                ]);

            $this->management->destroy($duplicate, $actor);
        });

        $survivor->refresh();
        $duplicate->refresh();

        $this->auditLog->record(
            Log::ACTION_MERGE_CONTACT,
            $actor,
            $survivor,
            [
                'before' => $before,
                'after' => [
                    'survivor' => $survivor->auditSnapshot(),
                    'duplicate' => $duplicate->auditSnapshot(),
                ],
            ]
        );

        return $survivor;
    }
}
