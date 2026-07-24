<?php

namespace App\Services\Companies;

use App\Actions\RestoreResource;
use App\Models\Company;
use App\Models\Log;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class RestorerService
{
    /**
     * Inject the required services into the restorer service.
     */
    public function __construct(
        protected readonly AuditLogService $auditLogService,
        protected readonly RestoreResource $restoreResource,
    ) {}

    /**
     * Restore a soft-deleted company.
     *
     * @throws \Exception
     */
    public function restore(
        Company $company,
        int $restoredBy,
        ?User $actor = null
    ): Company {
        $actor ??= User::findOrFail(
            $restoredBy
        );

        return $this->restoreResource->handle(
            $company,
            function (Company $company) use ($actor, $restoredBy): void {
                $company->restored_by = $restoredBy;
                $company->restored_at = now();
                $company->save();

                $this->auditLogService->record(
                    Log::ACTION_RESTORE_COMPANY,
                    $actor,
                    $company,
                    [
                        'before' => $this->auditLogService->snapshot(
                            $company
                        ),
                    ],
                );
            }
        );
    }

    /**
     * Restore multiple soft-deleted companies.
     *
     * @return int Number of companies restored
     *
     * @throws \Exception
     */
    public function restoreMultiple(
        array $companyIds,
        int $restoredBy
    ): int {
        $count = 0;

        DB::transaction(function () use (
            $companyIds,
            $restoredBy,
            &$count
        ) {
            $actor = User::findOrFail(
                $restoredBy
            );

            /** @var Collection<int, Company> $companies */
            $companies = Company::withTrashed()
                ->whereIn('id', $companyIds)
                ->get();

            foreach ($companies as $company) {
                if ($company->trashed()) {
                    $this->restore(
                        $company,
                        $restoredBy,
                        $actor
                    );
                    $count++;
                }
            }
        });

        return $count;
    }
}
