<?php

namespace App\Services\Categories;

use App\Models\Category;
use App\Services\SlugService;

class DataPreparationService
{
    public function __construct(
        protected SlugService $slugService,
    ) {}

    /**
     * Prepare category data for creation.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function prepareForCreation(
        array $data, int $createdBy
    ): array {
        return [
            'parent_id' => $data['parent_id'] ?? null,
            'name' => $data['name'],
            'slug' => $data['slug'] ?? $this->slugService->generateUnique(
                Category::class,
                $data['name']
            ),
            'description' => $data['description'] ?? null,
            'meta' => $data['meta'] ?? null,
            'created_by' => $createdBy,
        ];
    }

    /**
     * Prepare category data for update.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function prepareForUpdate(
        array $data,
        int $updatedBy,
        ?int $id = null
    ): array {
        $allowed = [
            'parent_id',
            'name',
            'slug',
            'description',
            'meta',
        ];

        $payload = [];

        foreach ($allowed as $field) {
            if (array_key_exists($field, $data)) {
                $payload[$field] = $data[$field];
            }
        }

        // Regenerate the slug if the name changes but no explicit slug was given,
        // so it stays in sync rather than going stale against a renamed category.
        if (array_key_exists('name', $payload) && ! array_key_exists('slug', $data)) {
            $payload['slug'] = $this->slugService->generateUnique(Category::class, $payload['name'], $id);
        }

        $payload['updated_by'] = $updatedBy;

        return $payload;
    }
}
