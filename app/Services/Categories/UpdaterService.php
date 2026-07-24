<?php

namespace App\Services\Categories;

use App\Actions\UpdateResource;
use App\Models\Category;
use App\Models\Log;
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
     * Update an existing category.
     *
     * @param  array<string, mixed>  $data
     *
     * @throws \Exception
     */
    public function update(
        Category $category,
        array $data,
        int $updatedBy
    ): Category {
        $actor = User::findOrFail(
            $updatedBy
        );

        $before = $this->auditLogService->snapshot(
            $category
        );

        $categoryData = $this->dataPreparation->prepareForUpdate(
            $data,
            $updatedBy,
            $category->id
        );

        return $this->updateResource->handle(
            $category,
            $categoryData,
            function (Category $category) use ($actor, $before): void {
                $fresh = $category->fresh();

                $this->auditLogService->record(
                    Log::ACTION_UPDATE_CATEGORY,
                    $actor,
                    $fresh,
                    [
                        'before' => $before,
                        'after' => $this->auditLogService->snapshot(
                            $fresh
                        ),
                    ],
                );
            }
        );
    }
}
