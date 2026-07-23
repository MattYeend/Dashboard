<?php

namespace App\Services\Categories;

use App\Actions\CreateResource;
use App\Models\Category;
use App\Models\Log;
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
     * Create a new category.
     *
     * @param  array<string, mixed>  $data
     *
     * @throws ModelNotFoundException
     */
    public function create(array $data, int $createdBy): Category
    {
        $actor = User::findOrFail($createdBy);

        return $this->createResource->handle(
            $data,
            function (array $data) use ($createdBy, $actor): Category {
                $categoryData = $this->dataPreparation->prepareForCreation(
                    $data,
                    $createdBy
                );

                $newCategory = Category::create(
                    $categoryData
                );

                $this->auditLogService->record(
                    Log::ACTION_CREATE_CATEGORY,
                    $actor,
                    $newCategory,
                    ['after' => $this->auditLogService->snapshot($newCategory)],
                );

                return $newCategory;
            });
    }
}
