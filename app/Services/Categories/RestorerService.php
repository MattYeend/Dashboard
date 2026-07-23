<?php

namespace App\Services\Categories;

use App\Actions\RestoreResource;
use App\Models\Category;
use App\Models\Log;
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
     * Restore a soft-deleted category.
     *
     * @throws \Exception
     */
    public function restore(
        Category $category,
        int $restoredBy,
        ?User $actor = null
    ): Category {
        $actor ??= User::findOrFail($restoredBy);

        return $this->restoreResource->handle(
            $category,
            function (Category $category) use ($actor, $restoredBy): void {
                $category->restored_by = $restoredBy;
                $category->restored_at = now();
                $category->save();

                $this->auditLogService->record(
                    Log::ACTION_RESTORE_INDUSTRY,
                    $actor,
                    $category,
                    ['before' => $this->auditLogService->snapshot($category)],
                );
            });
    }

    /**
     * Restore multiple soft-deleted categories.
     *
     * @return int Number of categories restored
     *
     * @throws \Exception
     */
    public function restoreMultiple(
        array $categoryIds,
        int $restoredBy
    ): int {
        $count = 0;

        DB::transaction(function () use (
            $categoryIds,
            $restoredBy,
            &$count
        ) {
            $actor = User::findOrFail($restoredBy);

            /** @var Collection<int, Category> $categories */
            $categories = Category::withTrashed()
                ->whereIn('id', $categoryIds)
                ->get();

            foreach ($categories as $category) {
                if ($category->trashed()) {
                    $this->restore(
                        $category,
                        $restoredBy,
                        $actor
                    );
                    $count++;
                }
            }
        });

        return $count;
    }
}
