<?php

declare(strict_types=1);

namespace App\Services\Products;

use App\Actions\DeleteResource;
use App\Models\Log;
use App\Models\Product;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Support\Facades\DB;

class DeleterService
{
    /**
     * Inject the required services into the deleter service.
     */
    public function __construct(
        protected readonly AuditLogService $auditLogService,
        protected readonly DeleteResource $deleteResource,
    ) {}

    /**
     * Soft delete a product.
     *
     * @throws \Exception
     */
    public function delete(
        Product $product,
        int $deletedBy,
        ?User $actor = null
    ): bool {
        $actor ??= User::findOrFail($deletedBy);

        return $this->deleteResource->handle(
            $product,
            function (Product $product) use ($actor, $deletedBy): void {
                $product->deleted_by = $deletedBy;
                $product->deleted_at = now();
                $product->save();

                $this->auditLogService->record(
                    Log::ACTION_DELETE_PRODUCT,
                    $actor,
                    $product,
                    ['before' => $this->auditLogService->snapshot($product)],
                );
            });
    }

    /**
     * Force delete a product (permanent deletion).
     *
     * @throws \Exception
     */
    public function forceDelete(
        Product $product,
        int $deletedBy
    ): bool {
        $actor = User::findOrFail($deletedBy);

        return $this->deleteResource->forceHandle(
            $product,
            function (Product $product) use ($actor): void {
                $this->auditLogService->record(
                    Log::ACTION_FORCE_DELETE_PRODUCT,
                    $actor,
                    $product,
                    ['before' => $this->auditLogService->snapshot($product)],
                );
            });
    }

    /**
     * Delete multiple products.
     *
     * @param  array<int, int>  $productIds
     *
     * @throws \Exception
     */
    public function deleteMultiple(
        array $productIds,
        int $deletedBy
    ): int {
        $count = 0;

        DB::transaction(function () use ($productIds, $deletedBy, &$count) {
            $actor = User::findOrFail($deletedBy);
            $products = Product::whereIn('id', $productIds)->get();

            foreach ($products as $product) {
                if ($this->delete($product, $deletedBy, $actor)) {
                    $count++;
                }
            }
        });

        return $count;
    }
}