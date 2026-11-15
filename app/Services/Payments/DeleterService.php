<?php

declare(strict_types=1);

namespace App\Services\Payments;

use App\Actions\DeleteResource;
use App\Models\Log;
use App\Models\Payment;
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
     * Soft delete a payment that has not completed.
     *
     * @throws AuthorizationException
     */
    public function delete(
        Payment $payment,
        int $deletedBy,
        ?User $actor = null
    ): bool {
        $actor ??= User::findOrFail($deletedBy);

        return DB::transaction(function () use ($payment, $deletedBy, $actor): bool {
            $locked = Payment::query()
                ->with('status')
                ->whereKey($payment->id)
                ->lockForUpdate()
                ->firstOrFail();

            // Second layer: Super Admin bypasses the policy through Gate::before.
            if (! $this->activeChecker->canBeDeleted($locked)) {
                throw new AuthorizationException('Completed payments and refunds cannot be deleted.');
            }

            return $this->deleteResource->handle(
                $locked,
                function (Payment $payment) use ($actor, $deletedBy): void {
                    $payment->deleted_by = $deletedBy;
                    $payment->deleted_at = now();
                    $payment->save();

                    $this->auditLogService->record(
                        Log::ACTION_DELETE_PAYMENT,
                        $actor,
                        $payment,
                        ['before' => $this->auditLogService->snapshot($payment)],
                    );
                });
        });
    }

    /**
     * Permanently delete a trashed, non-completed payment.
     *
     * @throws AuthorizationException
     */
    public function forceDelete(
        Payment $payment,
        int $deletedBy
    ): bool {
        if (! $this->activeChecker->canBeForceDeleted($payment)) {
            throw new AuthorizationException('Only a trashed, non-completed payment can be permanently deleted.');
        }

        $actor = User::findOrFail($deletedBy);

        return $this->deleteResource->forceHandle(
            $payment,
            function (Payment $payment) use ($actor): void {
                $this->auditLogService->record(
                    Log::ACTION_FORCE_DELETE_PAYMENT,
                    $actor,
                    $payment,
                    ['before' => $this->auditLogService->snapshot($payment)],
                );
            });
    }
}