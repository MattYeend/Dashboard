<?php

declare(strict_types=1);

namespace App\Services\Payments;

use App\Actions\UpdateResource;
use App\Models\Log;
use App\Models\Payment;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;

class UpdaterService
{
    /**
     * Inject the required services into the updater service.
     */
    public function __construct(
        protected readonly DataPreparationService $dataPreparation,
        protected readonly ActiveCheckerService $activeChecker,
        protected readonly AuditLogService $auditLogService,
        protected readonly UpdateResource $updateResource,
    ) {}

    /**
     * Update a payment that has not completed.
     *
     * @param  array<string, mixed>  $data
     *
     * @throws AuthorizationException
     */
    public function update(
        Payment $payment,
        array $data,
        int $updatedBy
    ): Payment {
        $actor = User::findOrFail($updatedBy);

        return DB::transaction(function () use ($payment, $data, $updatedBy, $actor): Payment {
            $locked = Payment::query()
                ->with('status')
                ->whereKey($payment->id)
                ->lockForUpdate()
                ->firstOrFail();

            // Second layer: Super Admin bypasses the policy through Gate::before.
            if (! $this->activeChecker->canBeModified($locked)) {
                throw new AuthorizationException('Completed payments and refunds cannot be edited.');
            }

            $before = $this->auditLogService->snapshot($locked);

            return $this->updateResource->handle(
                $locked,
                $this->dataPreparation->prepareForUpdate($data),
                function (Payment $payment) use ($actor, $before, $updatedBy): void {
                    $payment->forceFill(['updated_by' => $updatedBy])->save();

                    $fresh = $payment->fresh();

                    $this->auditLogService->record(
                        Log::ACTION_UPDATE_PAYMENT,
                        $actor,
                        $fresh,
                        [
                            'before' => $before,
                            'after' => $this->auditLogService->snapshot($fresh),
                        ],
                    );
                });
        });
    }
}
