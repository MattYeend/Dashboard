<?php

namespace App\Services\Addresses;

use App\Http\Requests\StoreAddressRequest;
use App\Http\Requests\UpdateAddressRequest;
use App\Models\Address;
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
     * Create a new company address.
     */
    public function store(StoreAddressRequest $request): Address
    {
        return $this->creator->create(
            $request->validated(),
            $request->user()->id
        );
    }

    /**
     * Update an existing address.
     */
    public function update(
        UpdateAddressRequest $request,
        Address $address
    ): Address {
        return $this->updater->update(
            $address,
            $request->validated(),
            $request->user()->id
        );
    }

    /**
     * Soft delete a address.
     */
    public function destroy(
        Address $address,
        User $actor
    ): void {
        $this->destructor->delete($address, $actor->id);
    }

    /**
     * Restore a soft-deleted address.
     */
    public function restore(
        int $id,
        User $actor
    ): Address {
        $address = Address::withTrashed()->findOrFail($id);

        return $this->restorer->restore($address, $actor->id);
    }

    /**
     * Force delete a address, permanently removing it from the
     * database.
     */
    public function forceDelete(
        int $id,
        User $actor
    ): void {
        $address = Address::withTrashed()->findOrFail($id);
        $this->destructor->forceDelete($address, $actor->id);
    }

    /**
     * Bulk restore addresses.
     */
    public function bulkRestore(
        array $ids,
        User $actor,
        callable $authoriseCallback
    ): array {
        $requestedIds = collect($ids)->unique()->values();

        $addresses = Address::onlyTrashed()
            ->whereIn('id', $requestedIds)
            ->get();

        $restored = [];

        foreach ($addresses as $address) {
            /** @var Address $address */
            $authoriseCallback($address);
            $this->restorer->restore($address, $actor->id);
            $restored[] = $address->id;
        }

        return [
            'restored' => $restored,
            'skipped' => $requestedIds
                ->diff($addresses->pluck('id'))
                ->values()
                ->all(),
        ];
    }

    /**
     * Bulk soft delete addresses.
     */
    public function bulkDelete(
        array $ids,
        User $actor,
        callable $authoriseCallback
    ): array {
        $deleted = [];

        foreach ($ids as $id) {
            $address = Address::findOrFail($id);
            $authoriseCallback($address);

            $this->destructor->delete($address, $actor->id);
            $deleted[] = $id;
        }

        return $deleted;
    }
}
