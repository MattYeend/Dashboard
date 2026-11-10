<?php

declare(strict_types=1);

namespace App\Services\Products;

use App\Actions\RestoreResource;
use App\Models\Log;
use App\Models\Product;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class RestorerService
{
    /**
     * Inject the required services into the restorer service.
     */
    public function __construct(
        protected readonly AuditLogService $auditLogService,
        protected readonly RestoreResource $restoreResource,
    ) {}

    /**
     * Restore a soft-deleted product.
     *
     * @throws \Exception
     */
    public function restore(
        Product $product,
        int $restoredBy,
        ?User $actor = null
    ): Product {
        $actor ??= User::findOrFail($restoredBy);

        return $this->restoreResource->handle(
            $product,
            function (Product $product) use ($actor, $restoredBy): void {
                $product->restored_by = $restoredBy;
                $product->restored_at = now();
                $product->save();

                $this->auditLogService->record(
                    Log::ACTION_RESTORE_PRODUCT,
                    $actor,
                    $product,
                    ['before' => $this->auditLogService->snapshot($product)],
                );
            });
    }

    /**
     * Restore multiple soft-deleted products.
     *
     * @param  array<int, int>  $productIds
     * @return int Number of products restored
     *
     * @throws \Exception
     */
    public function restoreMultiple(
        array $productIds,
        int $restoredBy
    ): int {
        $count = 0;

        DB::transaction(function () use ($productIds, $restoredBy, &$count) {
            $actor = User::findOrFail($restoredBy);

            /** @var Collection<int,Product> $products */
            $products = Product::withTrashed()
                ->whereIn('id', $productIds)
                ->get();

            foreach ($products as $product) {
                if ($product->trashed()) {
                    $this->restore($product, $restoredBy, $actor);
                    $count++;
                }
            }
        });

        return $count;
    }
}