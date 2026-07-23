<?php

namespace App\Services\Addresses;

use App\Actions\RestoreResource;
use App\Models\Address;
use App\Models\Log;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class RestorerService
{
    /**
     * Inject the required services into the resorer service.
     */
    public function __construct(
        protected readonly AuditLogService $auditLogService,
        protected readonly RestoreResource $restoreResource,
    ) {}

    /**
     * Restore a soft-deleted address.
     *
     * @throws \Exception
     */
    public function restore(
        Address $address,
        int $restoredBy,
        ?User $actor = null
    ): Address {
        $actor ??= User::findOrFail($restoredBy);

        return $this->restoreResource->handle(
            $address,
            function (Address $address) use ($actor, $restoredBy): void {
                $address->restored_by = $restoredBy;
                $address->restored_at = now();
                $address->save();

                $this->auditLogService->record(
                    Log::ACTION_RESTORE_ADDRESS,
                    $actor,
                    $address,
                    ['before' => $this->auditLogService->snapshot($address)],
                );
            });
    }

    /**
     * Restore multiple soft-deleted addresses.
     *
     * @return int Number of addresses restored
     *
     * @throws \Exception
     */
    public function restoreMultiple(
        array $addressIds,
        int $restoredBy
    ): int {
        $count = 0;

        DB::transaction(function () use (
            $addressIds,
            $restoredBy,
            &$count
        ) {
            $actor = User::findOrFail($restoredBy);

            /** @var Collection<int,Address> $addresses */
            $addresses = Address::withTrashed()
                ->whereIn('id', $addressIds)
                ->get();

            foreach ($addresses as $address) {
                if ($address->trashed()) {
                    $this->restore($address, $restoredBy, $actor);
                    $count++;
                }
            }
        });

        return $count;
    }
}
