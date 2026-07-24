<?php

namespace App\Services\Categories;

use App\Actions\DeleteResource;
use App\Models\Category;
use App\Models\Log;
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
     * Soft delete an category.
     *
     * @throws \Exception
     */
    public function delete(
        Category $category,
        int $deletedBy,
        ?User $actor = null
    ): bool {
        $actor ??= User::findOrFail($deletedBy);

        return $this->deleteResource->handle(
            $category,
            function (Category $category) use ($actor, $deletedBy): void {
                $category->deleted_by = $deletedBy;
                $category->deleted_at = now();
                $category->save();

                $this->auditLogService->record(
                    Log::ACTION_DELETE_CATEGORY,
                    $actor,
                    $category,
                    [
                        'before' => $this->auditLogService->snapshot(
                            $category
                        ),
                    ],
                );
            }
        );
    }

    /**
     * Force delete an category (permanent deletion).
     *
     * @throws \Exception
     */
    public function forceDelete(
        Category $category,
        int $deletedBy
    ): bool {
        $actor = User::findOrFail(
            $deletedBy
        );

        return $this->deleteResource->forceHandle(
            $category,
            function (Category $category) use ($actor): void {
                $this->auditLogService->record(
                    Log::ACTION_FORCE_DELETE_CATEGORY,
                    $actor,
                    $category,
                    [
                        'before' => $this->auditLogService->snapshot(
                            $category
                        ),
                    ],
                );
            }
        );
    }

    /**
     * Delete multiple categories.
     *
     * @throws \Exception
     */
    public function deleteMultiple(
        array $categoryIds,
        int $deletedBy
    ): int {
        $count = 0;

        DB::transaction(function () use (
            $categoryIds,
            $deletedBy,
            &$count
        ) {
            $actor = User::findOrFail(
                $deletedBy
            );
            $categories = Category::whereIn('id', $categoryIds)->get();

            foreach ($categories as $category) {
                if ($this->delete(
                    $category,
                    $deletedBy,
                    $actor
                )) {
                    $count++;
                }
            }
        });

        return $count;
    }
}
