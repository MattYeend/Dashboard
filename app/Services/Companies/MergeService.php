<?php

namespace App\Services\Companies;

use App\Models\Company;
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
     * Merge the duplicate company into the survivor, reassigning
     * contacts, addresses, orders, and deals, then soft-deleting
     * the duplicate.
     */
    public function merge(Company $survivor, Company $duplicate, User $actor): Company
    {
        if ($survivor->is($duplicate)) {
            throw new InvalidArgumentException('A company cannot be merged into itself.');
        }

        $before = [
            'survivor' => $survivor->auditSnapshot(),
            'duplicate' => $duplicate->auditSnapshot(),
        ];

        DB::transaction(function () use ($survivor, $duplicate, $actor): void {
            $duplicate->contacts()->update([
                'contactable_id' => $survivor->id,
                'contactable_type' => $survivor->getMorphClass(),
            ]);

            $duplicate->addresses()->update([
                'addressable_id' => $survivor->id,
                'addressable_type' => $survivor->getMorphClass(),
            ]);

            $duplicate->orders()->update([
                'orderable_id' => $survivor->id,
                'orderable_type' => $survivor->getMorphClass(),
            ]);

            $duplicate->deals()->update([
                'company_id' => $survivor->id,
            ]);

            $this->management->destroy($duplicate, $actor);
        });

        $survivor->refresh();
        $duplicate->refresh();

        $this->auditLog->record(
            Log::ACTION_MERGE_COMPANY,
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
