<?php

namespace App\Services\Labels;

use App\Models\Label;
use App\Services\SlugService;

class DataPreparationService
{
    public function __construct(
        protected SlugService $slugService,
    ) {}

    /**
     * Prepare label data for creation.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function prepareForCreation(array $data, int $createdBy): array
    {
        return [
            'name' => $data['name'],
            'slug' => $data['slug'] ?? $this->slugService->generateUnique(Label::class, $data['name']),
            'background_colour' => $data['background_colour'] ?? '#6b7280',
            'text_colour' => $data['text_colour'] ?? '#ffffff',
            'meta' => $data['meta'] ?? null,
            'created_by' => $createdBy,
        ];
    }

    /**
     * Prepare label data for update.
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
            'background_colour',
            'text_colour',
            'meta',
        ];

        $payload = [];

        foreach ($allowed as $field) {
            if (array_key_exists($field, $data)) {
                $payload[$field] = $data[$field];
            }
        }

        // Regenerate the slug if the name changes but no explicit slug was given,
        // so it stays in sync rather than going stale against a renamed label.
        if (array_key_exists('name', $payload) && ! array_key_exists('slug', $data)) {
            $payload['slug'] = $this->slugService->generateUnique(Label::class, $payload['name'], $id);
        }

        $payload['updated_by'] = $updatedBy;

        return $payload;
    }
}
