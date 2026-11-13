<?php

declare(strict_types=1);

namespace App\Services\PaymentStatuses;

use App\Actions\DeleteResource;
use App\Models\Log;
use App\Models\PaymentStatus;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;

class DeleterService
{
    /**
     * Inject the required services into the deleter service.
     */
    public function __construct(
        protected readonly AuditLogService $auditLogService,
        protected readonly ActiveCheckerService $activeChecker,
        protected readonly DeleteResource $deleteResource,
    ) {}

    /**
     * Soft delete a payment status.
     *
     * @throws AuthorizationException
     * @throws \Exception
     */
    public function delete(
        PaymentStatus $paymentStatus,
        int $deletedBy,
        ?User $actor = null
    ): bool {
        if (! $this->activeChecker->canBeDeleted($paymentStatus)) {
            throw new AuthorizationException('System statuses cannot be deleted.');
        }

        $actor ??= User::findOrFail($deletedBy);

        return $this->deleteResource->handle(
            $paymentStatus,
            function (PaymentStatus $paymentStatus) use ($actor, $deletedBy): void {
                $paymentStatus->deleted_by = $deletedBy;
                $paymentStatus->deleted_at = now();
                $paymentStatus->save();

                $this->auditLogService->record(
                    Log::ACTION_DELETE_PAYMENT_STATUS,
                    $actor,
                    $paymentStatus,
                    ['before' => $this->auditLogService->snapshot($paymentStatus)],
                );
            });
    }

    /**
     * Force delete a payment status (permanent deletion).
     *
     * @throws AuthorizationException
     * @throws \Exception
     */
    public function forceDelete(
        PaymentStatus $paymentStatus,
        int $deletedBy
    ): bool {
        if ($paymentStatus->isSystem()) {
            throw new AuthorizationException('System statuses cannot be deleted.');
        }

        $actor = User::findOrFail($deletedBy);

        return $this->deleteResource->forceHandle(
            $paymentStatus,
            function (PaymentStatus $paymentStatus) use ($actor): void {
                $this->auditLogService->record(
                    Log::ACTION_FORCE_DELETE_PAYMENT_STATUS,
                    $actor,
                    $paymentStatus,
                    ['before' => $this->auditLogService->snapshot($paymentStatus)],
                );
            });
    }

    /**
     * Delete multiple payment statuses.
     *
     * @param  array<int, int>  $paymentStatusIds
     *
     * @throws \Exception
     */
    public function deleteMultiple(
        array $paymentStatusIds,
        int $deletedBy
    ): int {
        $count = 0;

        DB::transaction(function () use ($paymentStatusIds, $deletedBy, &$count) {
            $actor = User::findOrFail($deletedBy);
            $paymentStatuses = PaymentStatus::whereIn('id', $paymentStatusIds)->get();

            foreach ($paymentStatuses as $paymentStatus) {
                if ($this->delete($paymentStatus, $deletedBy, $actor)) {
                    $count++;
                }
            }
        });

        return $count;
    }
}
