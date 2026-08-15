<?php

namespace App\Services\Tags;

use App\Models\Tag;
use App\Services\SlugService;

class DataPreparationService
{
    public function __construct(
        protected SlugService $slugService,
    ) {}

    /**
     * Prepare tag data for creation.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function prepareForCreation(array $data): array
    {
        $name = strip_tags($data['name']);

        return [
            'name' => $name,
            'slug' => $data['slug'] ?? $this->slugService->generateUnique(Tag::class, $data['name']),
            'meta' => $data['meta'] ?? null,
        ];
    }

    /**
     * Prepare tag data for update.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function prepareForUpdate(
        array $data,
        ?int $id = null
    ): array {
        $payload = [];

        if (array_key_exists('name', $data)) {
            $payload['name'] = strip_tags($data['name']);
        }

        if (array_key_exists('name', $payload) && ! array_key_exists('slug', $data)) {
            $payload['slug'] = $this->slugService->generateUnique(Tag::class, $payload['name'], $id);
        }

        if (array_key_exists('meta', $data)) {
            $payload['meta'] = $data['meta'];
        }

        return $payload;
    }
}
