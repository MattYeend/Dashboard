<?php

namespace App\Services\Companies;

use App\Http\Requests\Companies\StoreCompanyRequest;
use App\Http\Requests\Companies\UpdateCompanyRequest;
use App\Models\Company;
use App\Models\User;

class ManagementService
{
    /**
     * Inject the required services into the management service.
     */
    public function __construct(
        protected readonly CreatorService $creator,
        protected readonly UpdaterService $updater,
        protected readonly DeleterService $destructor,
        protected readonly RestorerService $restorer
    ) {}

    /**
     * Create a new company.
     */
    public function store(
        StoreCompanyRequest $request
    ): Company {
        return $this->creator->create(
            $request->validated(),
            $request->user()->id
        );
    }

    /**
     * Update an existing company.
     */
    public function update(
        UpdateCompanyRequest $request,
        Company $company
    ): Company {
        return $this->updater->update(
            $company,
            $request->validated(),
            $request->user()->id
        );
    }

    /**
     * Soft delete a company.
     */
    public function destroy(
        Company $company,
        User $actor
    ): void {
        $this->destructor->delete($company, $actor->id);
    }

    /**
     * Restore a soft-deleted company.
     */
    public function restore(
        int $id,
        User $actor
    ): Company {
        $company = Company::withTrashed()->findOrFail($id);

        return $this->restorer->restore($company, $actor->id);
    }

    /**
     * Force delete a company, permanently removing it from the database.
     */
    public function forceDelete(
        int $id,
        User $actor
    ): void {
        $company = Company::withTrashed()->findOrFail($id);
        $this->destructor->forceDelete($company, $actor->id);
    }

    /**
     * Bulk restore companies.
     */
    public function bulkRestore(
        array $ids,
        User $actor,
        callable $authoriseCallback
    ): array {
        $requestedIds = collect($ids)->unique()->values();

        $companies = Company::onlyTrashed()
            ->whereIn('id', $requestedIds)
            ->get();

        $restored = [];

        foreach ($companies as $company) {
            /** @var Company $company */
            $authoriseCallback($company);
            $this->restorer->restore($company, $actor->id);
            $restored[] = $company->id;
        }

        return [
            'restored' => $restored,
            'skipped' => $requestedIds
                ->diff($companies->pluck('id'))
                ->values()
                ->all(),
        ];
    }

    /**
     * Bulk soft delete companies.
     */
    public function bulkDelete(
        array $ids,
        User $actor,
        callable $authoriseCallback
    ): array {
        $deleted = [];

        foreach ($ids as $id) {
            $company = Company::findOrFail($id);
            $authoriseCallback($company);

            $this->destructor->delete($company, $actor->id);
            $deleted[] = $id;
        }

        return $deleted;
    }
}
