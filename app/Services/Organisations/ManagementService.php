<?php

namespace App\Services\Organisations;

use App\Http\Requests\Organisations\StoreOrganisationRequest;
use App\Http\Requests\Organisations\UpdateOrganisationRequest;
use App\Models\Organisation;
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
        protected readonly RestorerService $restorer,
    ) {}

    /**
     * Create a new organisation.
     */
    public function store(StoreOrganisationRequest $request): Organisation
    {
        return $this->creator->create($request->validated(), $request->user()->id);
    }

    /**
     * Update an existing organisation.
     */
    public function update(UpdateOrganisationRequest $request, Organisation $organisation): Organisation
    {
        return $this->updater->update($organisation, $request->validated(), $request->user()->id);
    }

    /**
     * Soft delete an organisation.
     */
    public function destroy(Organisation $organisation, User $actor): void
    {
        $this->destructor->delete($organisation, $actor->id);
    }

    /**
     * Restore a soft-deleted organisation.
     */
    public function restore(int $id, User $actor): Organisation
    {
        $organisation = Organisation::withTrashed()->findOrFail($id);

        return $this->restorer->restore($organisation, $actor->id);
    }

    /**
     * Force delete an organisation, permanently removing it from the database.
     */
    public function forceDelete(int $id, User $actor): void
    {
        $organisation = Organisation::withTrashed()->findOrFail($id);
        $this->destructor->forceDelete($organisation, $actor->id);
    }

    /**
     * Bulk restore organisations.
     *
     * @param  array<int, int>  $ids
     * @return array{restored: array<int, int>, skipped: array<int, int>}
     */
    public function bulkRestore(array $ids, User $actor, callable $authoriseCallback): array
    {
        $requestedIds = collect($ids)->unique()->values();

        $organisations = Organisation::onlyTrashed()->whereIn('id', $requestedIds)->get();

        $restored = [];

        foreach ($organisations as $organisation) {
            /** @var Organisation $organisation */
            $authoriseCallback($organisation);
            $this->restorer->restore($organisation, $actor->id);
            $restored[] = $organisation->id;
        }

        return [
            'restored' => $restored,
            'skipped' => $requestedIds->diff($organisations->pluck('id'))->values()->all(),
        ];
    }

    /**
     * Bulk soft delete organisations.
     *
     * @param  array<int, int>  $ids
     * @return array{deleted: array<int, int>, skipped: array<int, int>}
     */
    public function bulkDelete(array $ids, User $actor, callable $authoriseCallback): array
    {
        $requestedIds = collect($ids)->unique()->values();

        $organisations = Organisation::whereIn('id', $requestedIds)->get();

        $deleted = [];

        foreach ($organisations as $organisation) {
            /** @var Organisation $organisation */
            $authoriseCallback($organisation);
            $this->destructor->delete($organisation, $actor->id);
            $deleted[] = $organisation->id;
        }

        return [
            'deleted' => $deleted,
            'skipped' => $requestedIds->diff($organisations->pluck('id'))->values()->all(),
        ];
    }
}
