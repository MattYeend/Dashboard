<?php

declare(strict_types=1);

namespace App\Services\Products;

use App\Actions\UpdateResource;
use App\Models\Log;
use App\Models\Product;
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
     * Update an existing product.
     *
     * @param  array<string, mixed>  $data
     *
     * @throws \Exception
     */
    public function update(
        Product $product,
        array $data,
        int $updatedBy
    ): Product {
        $actor = User::findOrFail($updatedBy);

        $before = $this->auditLogService->snapshot($product);

        $productData = $this->dataPreparation->prepareForUpdate($data);

        return $this->updateResource->handle(
            $product,
            $productData,
            function (Product $product) use ($actor, $before, $updatedBy): void {
                $product->forceFill([
                    'updated_by' => $updatedBy,
                ])->save();

                $fresh = $product->fresh();

                $this->auditLogService->record(
                    Log::ACTION_UPDATE_PRODUCT,
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