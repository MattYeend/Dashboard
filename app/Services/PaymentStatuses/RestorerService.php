<?php

declare(strict_types=1);

namespace App\Services\PaymentStatuses;

use App\Actions\RestoreResource;
use App\Models\Log;
use App\Models\PaymentStatus;
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
     * Restore a soft-deleted payment status.
     *
     * @throws \Exception
     */
    public function restore(
        PaymentStatus $paymentStatus,
        int $restoredBy,
        ?User $actor = null
    ): PaymentStatus {
        $actor ??= User::findOrFail($restoredBy);

        return $this->restoreResource->handle(
            $paymentStatus,
            function (PaymentStatus $paymentStatus) use ($actor, $restoredBy): void {
                $paymentStatus->restored_by = $restoredBy;
                $paymentStatus->restored_at = now();
                $paymentStatus->save();

                $this->auditLogService->record(
                    Log::ACTION_RESTORE_PAYMENT_STATUS,
                    $actor,
                    $paymentStatus,
                    ['before' => $this->auditLogService->snapshot($paymentStatus)],
                );
            });
    }

    /**
     * Restore multiple soft-deleted payment statuses.
     *
     * @param  array<int, int>  $paymentStatusIds
     * @return int Number of payment statuses restored
     *
     * @throws \Exception
     */
    public function restoreMultiple(
        array $paymentStatusIds,
        int $restoredBy
    ): int {
        $count = 0;

        DB::transaction(function () use ($paymentStatusIds, $restoredBy, &$count) {
            $actor = User::findOrFail($restoredBy);

            /** @var Collection<int,PaymentStatus> $paymentStatuses */
            $paymentStatuses = PaymentStatus::withTrashed()
                ->whereIn('id', $paymentStatusIds)
                ->get();

            foreach ($paymentStatuses as $paymentStatus) {
                if ($paymentStatus->trashed()) {
                    $this->restore($paymentStatus, $restoredBy, $actor);
                    $count++;
                }
            }
        });

        return $count;
    }
}
