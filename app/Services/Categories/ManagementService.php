<?php

namespace App\Services\Categories;

use App\Http\Requests\Categories\ImportCategoryRequest;
use App\Http\Requests\Categories\StoreCategoryRequest;
use App\Http\Requests\Categories\UpdateCategoryRequest;
use App\Models\Category;
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
     * Create a new category.
     */
    public function store(
        StoreCategoryRequest $request
    ): Category {
        return $this->creator->create(
            $request->validated(),
            $request->user()->id
        );
    }

    /**
     * Update an existing category.
     */
    public function update(
        UpdateCategoryRequest $request,
        Category $category
    ): Category {
        return $this->updater->update(
            $category,
            $request->validated(),
            $request->user()->id
        );
    }

    /**
     * Soft delete a category.
     */
    public function destroy(
        Category $category,
        User $actor
    ): void {
        $this->destructor->delete($category, $actor->id);
    }

    /**
     * Restore a soft-deleted category.
     */
    public function restore(
        int $id,
        User $actor
    ): Category {
        $category = Category::withTrashed()->findOrFail($id);

        return $this->restorer->restore($category, $actor->id);
    }

    /**
     * Force delete a category, permanently removing it from the database.
     */
    public function forceDelete(
        int $id,
        User $actor
    ): void {
        $category = Category::withTrashed()->findOrFail($id);
        $this->destructor->forceDelete($category, $actor->id);
    }

    /**
     * Bulk restore categories.
     */
    public function bulkRestore(
        array $ids,
        User $actor,
        callable $authoriseCallback
    ): array {
        $requestedIds = collect($ids)->unique()->values();

        $categories = Category::onlyTrashed()
            ->whereIn('id', $requestedIds)
            ->get();

        $restored = [];

        foreach ($categories as $category) {
            /** @var Category $category */
            $authoriseCallback($category);
            $this->restorer->restore($category, $actor->id);
            $restored[] = $category->id;
        }

        return [
            'restored' => $restored,
            'skipped' => $requestedIds
                ->diff($categories->pluck('id'))
                ->values()
                ->all(),
        ];
    }

    /**
     * Bulk soft delete categories.
     */
    public function bulkDelete(
        array $ids,
        User $actor,
        callable $authoriseCallback
    ): array {
        $requestedIds = collect($ids)->unique()->values();

        $categories = Category::whereIn('id', $requestedIds)->get();

        $deleted = [];

        foreach ($categories as $category) {
            /** @var Category $category */
            $authoriseCallback($category);
            $this->destructor->delete($category, $actor->id);
            $deleted[] = $category->id;
        }

        return [
            'deleted' => $deleted,
            'skipped' => $requestedIds
                ->diff($categories->pluck('id'))
                ->values()
                ->all(),
        ];
    }

    /**
     * Import categories from an uploaded file.
     *
     * @return array{imported: int, skipped: array<int, array{row: int, reason: string}>}
     */
    public function import(
        ImportCategoryRequest $request
    ): array {
        return $this->importer->import(
            $request->file('file'),
            $request->user()->id
        );
    }

    /**
     * Export categories matching the given filters as a CSV download.
     *
     * @param  array<string, mixed>  $filters
     */
    public function export(array $filters): StreamedResponse
    {
        return $this->exporter->export($filters);
    }
}
