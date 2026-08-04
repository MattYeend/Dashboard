<?php

namespace App\Services\RegistrationInterests;

use App\Http\Requests\RegistrationInterests\StoreRegistrationInterestRequest;
use App\Models\RegistrationInterest;
use App\Models\User;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ManagementService
{
    public function __construct(
        protected CreatorService $creator,
        protected DeleterService $deleter,
        protected RestorerService $restorer,
        protected ExporterService $exporter,
    ) {}

    /**
     * Handle storing a new registration interest.
     */
    public function store(
        StoreRegistrationInterestRequest $request
    ): RegistrationInterest {
        return $this->creator->create($request->validated());
    }

    /**
     * Handle deleting a registration interest.
     */
    public function destroy(
        RegistrationInterest $interest,
        User $actor
    ): RegistrationInterest {
        return $this->deleter->delete($interest, $actor);
    }

    /**
     * Handle permanently deleting a registration interest.
     */
    public function forceDestroy(
        RegistrationInterest $interest,
        User $actor
    ): void {
        $this->deleter->forceDelete($interest, $actor);
    }

    /**
     * Handle restoring a soft-deleted registration interest.
     */
    public function restore(
        int $id,
        User $actor
    ): RegistrationInterest {
        return $this->restorer->restore($id, $actor);
    }

    /**
     * Handle bulk deleting registration interests.
     *
     * @param  array<int, int>  $ids
     */
    public function bulkDelete(
        array $ids,
        User $actor,
        callable $authorize
    ): array {
        return $this->deleter->bulkDelete($ids, $actor, $authorize);
    }

    /**
     * Handle bulk restoring registration interests.
     *
     * @param  array<int, int>  $ids
     */
    public function bulkRestore(
        array $ids,
        User $actor,
        callable $authorize
    ): array {
        return $this->restorer->bulkRestore($ids, $actor, $authorize);
    }

    /**
     * Export registration interests matching the given filters as a CSV download.
     *
     * @param  array<string, mixed>  $filters
     */
    public function export(array $filters): StreamedResponse
    {
        return $this->exporter->export($filters);
    }
}
