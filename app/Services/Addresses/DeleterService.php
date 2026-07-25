<?php

namespace App\Services\Addresses;

use App\Actions\DeleteResource;
use App\Models\Address;
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
     * Soft delete a address.
     *
     * @throws \Exception
     */
    public function delete(
        Address $address,
        int $deletedBy,
        ?User $actor = null
    ): bool {
        $actor ??= User::findOrFail($deletedBy);

        return $this->deleteResource->handle(
            $address,
            function (Address $address) use ($actor, $deletedBy): void {
                $address->deleted_by = $deletedBy;
                $address->deleted_at = now();
                $address->save();

                $this->auditLogService->record(
                    Log::ACTION_DELETE_ADDRESS,
                    $actor,
                    $address,
                    [
                        'before' => $this->auditLogService->snapshot(
                            $address
                        ),
                    ],
                );
            }
        );
    }

    /**
     * Force delete a address (permanent deletion).
     *
     * @throws \Exception
     */
    public function forceDelete(
        Address $address,
        int $deletedBy
    ): bool {
        $actor = User::findOrFail($deletedBy);

        return $this->deleteResource->forceHandle(
            $address,
            function (Address $address) use ($actor): void {
                $this->auditLogService->record(
                    Log::ACTION_FORCE_DELETE_ADDRESS,
                    $actor,
                    $address,
                    [
                        'before' => $this->auditLogService->snapshot(
                            $address
                        ),
                    ],
                );
            }
        );
    }

    /**
     * Delete multiple addresses.
     *
     * @throws \Exception
     */
    public function deleteMultiple(
        array $addressIds,
        int $deletedBy
    ): int {
        $count = 0;

        DB::transaction(function () use (
            $addressIds,
            $deletedBy,
            &$count
        ) {
            $actor = User::findOrFail($deletedBy);
            $addresses = Address::whereIn('id', $addressIds)->get();

            foreach ($addresses as $address) {
                if ($this->delete($address, $deletedBy, $actor)) {
                    $count++;
                }
            }
        });

        return $count;
    }
}
