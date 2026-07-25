<?php

namespace App\Services\Companies;

use App\Actions\DeleteResource;
use App\Models\Company;
use App\Models\Log;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Support\Facades\DB;

class DeleterService
{
    /**
     * Inject the required services into the deleter service.
     */
    public function __construct(
        protected readonly AuditLogService $auditLogService,
        protected readonly DeleteResource $deleteResource,
    ) {}

    /**
     * Soft delete an company.
     *
     * @throws \Exception
     */
    public function delete(
        Company $company,
        int $deletedBy,
        ?User $actor = null
    ): bool {
        $actor ??= User::findOrFail($deletedBy);

        return $this->deleteResource->handle(
            $company,
            function (Company $company) use ($actor, $deletedBy): void {
                $company->deleted_by = $deletedBy;
                $company->deleted_at = now();
                $company->save();

                $this->auditLogService->record(
                    Log::ACTION_DELETE_COMPANY,
                    $actor,
                    $company,
                    ['before' => $this->auditLogService->snapshot($company)],
                );
            });
    }

    /**
     * Force delete an company (permanent deletion).
     *
     * @throws \Exception
     */
    public function forceDelete(Company $company, int $deletedBy): bool
    {
        $actor = User::findOrFail($deletedBy);

        return $this->deleteResource->forceHandle(
            $company,
            function (Company $company) use ($actor): void {
                $this->auditLogService->record(
                    Log::ACTION_FORCE_DELETE_COMPANY,
                    $actor,
                    $company,
                    ['before' => $this->auditLogService->snapshot($company)],
                );
            });
    }

    /**
     * Delete multiple companies.
     *
     * @throws \Exception
     */
    public function deleteMultiple(array $companyIds, int $deletedBy): int
    {
        $count = 0;

        DB::transaction(function () use ($companyIds, $deletedBy, &$count) {
            $actor = User::findOrFail($deletedBy);
            $companies = Company::whereIn('id', $companyIds)->get();

            foreach ($companies as $company) {
                if ($this->delete($company, $deletedBy, $actor)) {
                    $count++;
                }
            }
        });

        return $count;
    }
}
