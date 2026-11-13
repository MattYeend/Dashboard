<?php

declare(strict_types=1);

namespace App\Services\PaymentStatuses;

use App\Actions\UpdateResource;
use App\Models\Log;
use App\Models\PaymentStatus;
use App\Models\User;
use App\Services\AuditLogService;

class UpdaterService
{
    /**
     * Inject the required services into the updater service.
     */
    public function __construct(
        protected readonly DataPreparationService $dataPreparation,
        protected readonly AuditLogService $auditLogService,
        protected readonly UpdateResource $updateResource,
    ) {}

    /**
     * Update an existing payment status.
     *
     * @param  array<string, mixed>  $data
     *
     * @throws \Exception
     */
    public function update(
        PaymentStatus $paymentStatus,
        array $data,
        int $updatedBy
    ): PaymentStatus {
        $actor = User::findOrFail($updatedBy);

        $before = $this->auditLogService->snapshot($paymentStatus);

        $paymentStatusData = $this->dataPreparation->prepareForUpdate($data, $paymentStatus);

        return $this->updateResource->handle(
            $paymentStatus,
            $paymentStatusData,
            function (PaymentStatus $paymentStatus) use ($actor, $before, $updatedBy): void {
                $paymentStatus->forceFill([
                    'updated_by' => $updatedBy,
                ])->save();

                $fresh = $paymentStatus->fresh();

                $this->auditLogService->record(
                    Log::ACTION_UPDATE_PAYMENT_STATUS,
                    $actor,
                    $fresh,
                    [
                        'before' => $before,
                        'after' => $this->auditLogService->snapshot($fresh),
                    ],
                );
            });
    }
}
