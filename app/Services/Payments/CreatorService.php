<?php

declare(strict_types=1);

namespace App\Services\Payments;

use App\Actions\CreateResource;
use App\Actions\Payments\RecordPayment;
use App\Models\Payment;
use App\Models\User;

class CreatorService
{
    /**
     * Inject the required services into the creator service.
     */
    public function __construct(
        protected readonly DataPreparationService $dataPreparation,
        protected readonly RecordPayment $recordPayment,
        protected readonly CreateResource $createResource,
    ) {}

    /**
     * Record a payment against an invoice.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data, int $createdBy): Payment
    {
        $actor = User::findOrFail($createdBy);

        return $this->createResource->handle(
            $this->dataPreparation->prepareForCreation($data),
            fn (array $paymentData): Payment => $this->recordPayment->handle($paymentData, $actor)
        );
    }
}