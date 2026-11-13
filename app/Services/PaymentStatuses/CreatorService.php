<?php

declare(strict_types=1);

namespace App\Services\PaymentStatuses;

use App\Actions\CreateResource;
use App\Models\Log;
use App\Models\PaymentStatus;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class CreatorService
{
    /**
     * Inject the required services into the creator service.
     */
    public function __construct(
        protected readonly DataPreparationService $dataPreparation,
        protected readonly AuditLogService $auditLogService,
        protected readonly CreateResource $createResource,
    ) {}

    /**
     * Create a new payment status.
     *
     * @param  array<string, mixed>  $data
     *
     * @throws ModelNotFoundException
     */
    public function create(array $data, int $createdBy): PaymentStatus
    {
        $actor = User::findOrFail($createdBy);

        return $this->createResource->handle(
            $data,
            function (array $data) use ($createdBy, $actor): PaymentStatus {
                $paymentStatusData = $this->dataPreparation->prepareForCreation($data);

                $newPaymentStatus = PaymentStatus::create($paymentStatusData);

                $newPaymentStatus->forceFill([
                    'created_by' => $createdBy,
                ])->save();

                $this->auditLogService->record(
                    Log::ACTION_CREATE_PAYMENT_STATUS,
                    $actor,
                    $newPaymentStatus,
                    ['after' => $this->auditLogService->snapshot($newPaymentStatus)],
                );

                return $newPaymentStatus;
            });
    }
}
