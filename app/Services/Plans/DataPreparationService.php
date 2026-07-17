<?php

namespace App\Services\Plans;

use App\Models\Plan;
use Illuminate\Support\Str;
use App\Services\SlugService;

class DataPreparationService
{
    public function __construct(
        protected SlugService $slugService,
    ) {}

    /**
     * Prepare plan data for creation.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function prepareForCreation(array $data, int $createdBy): array
    {
        return [
            'name' => $data['name'],
            'slug' => $data['slug'] ?? $this->slugService->generateUnique(Plan::class, $data['name']),
            'description' => $data['description'] ?? null,
            'price_per_user_per_month' => $data['price_per_user_per_month'],
            'is_active' => $data['is_active'] ?? true,
            'created_by' => $createdBy,
        ];
    }

    /**
     * Prepare plan data for update.
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
            'name',
            'slug',
            'description',
            'price_per_user_per_month',
            'is_active',
        ];

        $payload = [];

        foreach ($allowed as $field) {
            if (array_key_exists($field, $data)) {
                $payload[$field] = $data[$field];
            }
        }

        // Regenerate the slug if the name changes but no explicit slug was given,
        // so it stays in sync rather than going stale against a renamed plan.
        if (array_key_exists('name', $payload) && ! array_key_exists('slug', $data)) {
            $payload['slug'] = $this->slugService->generateUnique(Plan::class, $payload['name'], $id);
        }

        $payload['updated_by'] = $updatedBy;

        return $payload;
    }
}
