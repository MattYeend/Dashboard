<?php

declare(strict_types=1);

namespace App\Services\Products;

use App\Actions\CreateResource;
use App\Models\Log;
use App\Models\Product;
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
     * Create a new product.
     *
     * @param  array<string, mixed>  $data
     *
     * @throws ModelNotFoundException
     */
    public function create(array $data, int $createdBy): Product
    {
        $actor = User::findOrFail($createdBy);

        return $this->createResource->handle(
            $data,
            function (array $data) use ($createdBy, $actor): Product {
                $productData = $this->dataPreparation->prepareForCreation($data);

                $newProduct = Product::create($productData);

                $newProduct->forceFill([
                    'created_by' => $createdBy,
                ])->save();

                $this->auditLogService->record(
                    Log::ACTION_CREATE_PRODUCT,
                    $actor,
                    $newProduct,
                    ['after' => $this->auditLogService->snapshot($newProduct)],
                );

                return $newProduct;
            });
    }
}
