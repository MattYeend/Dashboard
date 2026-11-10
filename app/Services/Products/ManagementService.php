<?php

declare(strict_types=1);

namespace App\Services\Products;

use App\Http\Requests\Products\ImportProductRequest;
use App\Http\Requests\Products\StoreProductRequest;
use App\Http\Requests\Products\UpdateProductRequest;
use App\Models\Product;
use App\Models\User;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ManagementService
{
    /**
     * Inject the required services into the management service.
     */
    public function __construct(
        protected readonly CreatorService $creator,
        protected readonly UpdaterService $updater,
        protected readonly DeleterService $destructor,
        protected readonly RestorerService $restorer,
        protected readonly ImporterService $importer,
        protected readonly ExporterService $exporter,
    ) {}

    /**
     * Create a new product.
     */
    public function store(StoreProductRequest $request): Product
    {
        return $this->creator->create(
            $request->validated(),
            $request->user()->id
        );
    }

    /**
     * Update an existing product.
     */
    public function update(UpdateProductRequest $request, Product $product): Product
    {
        return $this->updater->update(
            $product,
            $request->validated(),
            $request->user()->id
        );
    }

    /**
     * Soft delete a product.
     */
    public function destroy(Product $product, User $actor): void
    {
        $this->destructor->delete($product, $actor->id);
    }

    /**
     * Restore a soft-deleted product.
     */
    public function restore(int $id, User $actor): Product
    {
        $product = Product::onlyTrashed()->findOrFail($id);

        return $this->restorer->restore($product, $actor->id);
    }

    /**
     * Force delete a product, permanently removing it from the database.
     */
    public function forceDelete(int $id, User $actor): void
    {
        $product = Product::onlyTrashed()->findOrFail($id);

        $this->destructor->forceDelete($product, $actor->id);
    }

    /**
     * Bulk restore products.
     *
     * @param  array<int, int>  $ids
     * @return array{restored: array<int, int>, skipped: array<int, int>}
     */
    public function bulkRestore(
        array $ids,
        User $actor,
        callable $authoriseCallback
    ): array {
        $requestedIds = collect($ids)->unique()->values();

        $productsById = Product::onlyTrashed()
            ->whereIn('id', $requestedIds)
            ->get()
            ->keyBy('id');

        $restored = [];
        $skipped = [];

        foreach ($requestedIds as $id) {
            $product = $productsById->get($id);

            if ($product === null) {
                $skipped[] = $id;

                continue;
            }

            $authoriseCallback($product);

            $this->restorer->restore($product, $actor->id);

            $restored[] = $product->id;
        }

        return [
            'restored' => $restored,
            'skipped' => $skipped,
        ];
    }

    /**
     * Bulk soft delete products.
     *
     * @param  array<int, int>  $ids
     * @return array{deleted: array<int, int>, skipped: array<int, int>}
     */
    public function bulkDelete(
        array $ids,
        User $actor,
        callable $authoriseCallback
    ): array {
        $requestedIds = collect($ids)->unique()->values();

        $productsById = Product::whereIn('id', $requestedIds)->get()->keyBy('id');

        $deleted = [];
        $skipped = [];

        foreach ($requestedIds as $id) {
            $product = $productsById->get($id);

            if ($product === null) {
                $skipped[] = $id;

                continue;
            }

            $authoriseCallback($product);

            $this->destructor->delete($product, $actor->id);

            $deleted[] = $product->id;
        }

        return [
            'deleted' => $deleted,
            'skipped' => $skipped,
        ];
    }

    /**
     * Import products from an uploaded file.
     *
     * @return array{imported: int, skipped: array<int, array{row: int, reason: string}>}
     */
    public function import(ImportProductRequest $request): array
    {
        return $this->importer->import(
            $request->file('file'),
            $request->user()->id
        );
    }

    /**
     * Export products matching the given filters as a CSV download.
     *
     * @param  array<string, mixed>  $filters
     */
    public function export(array $filters): StreamedResponse
    {
        return $this->exporter->export($filters);
    }

    /**
     * Expose the importer service for controller-level preview/commit actions.
     */
    public function importer(): ImporterService
    {
        return $this->importer;
    }
}
