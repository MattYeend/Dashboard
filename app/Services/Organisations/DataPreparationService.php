<?php

namespace App\Services\Organisations;

use App\Models\Organisation;
use App\Services\SlugService;

class DataPreparationService
{
    /**
     * Inject the slug service.
     */
    public function __construct(
        protected SlugService $slugService,
    ) {}

    /**
     * Prepare organisation data for creation.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function prepareForCreation(array $data): array
    {
        return [
            'name' => $data['name'],
            'slug' => $data['slug'] ?? $this->slugService->generateUnique(Organisation::class, $data['name']),
            'meta' => $data['meta'] ?? null,
        ];
    }

    /**
     * Prepare organisation data for update.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function prepareForUpdate(array $data, ?int $id = null): array
    {
        $allowed = ['name', 'slug', 'meta'];

        $payload = [];

        foreach ($allowed as $field) {
            if (array_key_exists($field, $data)) {
                $payload[$field] = $data[$field];
            }
        }

        // Regenerate the slug if the name changes but no explicit slug was given.
        if (array_key_exists('name', $payload) && ! array_key_exists('slug', $data)) {
            $payload['slug'] = $this->slugService->generateUnique(Organisation::class, $payload['name'], $id);
        }

        return $payload;
    }
}
