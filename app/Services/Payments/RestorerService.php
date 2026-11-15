<?php

declare(strict_types=1);

namespace App\Services\Payments;

use App\Actions\RestoreResource;
use App\Models\Log;
use App\Models\Payment;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Auth\Access\AuthorizationException;

class RestorerService
{
    /**
     * Inject the required services into the restorer service.
     */
    public function __construct(
        protected readonly AuditLogService $auditLogService,
        protected readonly ActiveCheckerService $activeChecker,
        protected readonly RestoreResource $restoreResource,
    ) {}

    /**
     * Restore a soft-deleted payment. It was never counted, so the
     * invoice balance is unaffected.
     *
     * @throws AuthorizationException
     */
    public function restore(
        Payment $payment,
        int $restoredBy,
        ?User $actor = null
    ): Payment {
        if (! $this->activeChecker->canBeRestoredOrForceDeleted($payment)) {
            throw new AuthorizationException('Only a trashed payment can be restored.');
        }

        $actor ??= User::findOrFail($restoredBy);

        return $this->restoreResource->handle(
            $payment,
            function (Payment $payment) use ($actor, $restoredBy): void {
                $payment->restored_by = $restoredBy;
                $payment->restored_at = now();
                $payment->save();

                $this->auditLogService->record(
                    Log::ACTION_RESTORE_PAYMENT,
                    $actor,
                    $payment,
                    ['before' => $this->auditLogService->snapshot($payment)],
                );
            });
    }
}