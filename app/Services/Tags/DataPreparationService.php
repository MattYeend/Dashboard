<?php

namespace App\Services\Tags;

use Illuminate\Support\Str;

class DataPreparationService
{
    /**
     * Prepare tag data for creation.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function prepareForCreation(array $data, int $createdBy): array
    {
        $name = strip_tags($data['name']);

        return [
            'name' => $name,
            'slug' => $this->resolveSlug($data['slug'] ?? null, $name),
            'meta' => $data['meta'] ?? null,
            'created_by' => $createdBy,
        ];
    }

    /**
     * Prepare tag data for update.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function prepareForUpdate(array $data, int $updatedBy): array
    {
        $payload = [];

        if (array_key_exists('name', $data)) {
            $payload['name'] = strip_tags($data['name']);
        }

        if (array_key_exists('slug', $data)) {
            $payload['slug'] = $this->resolveSlug($data['slug'], $payload['name'] ?? null);
        }

        if (array_key_exists('meta', $data)) {
            $payload['meta'] = $data['meta'];
        }

        $payload['updated_by'] = $updatedBy;

        return $payload;
    }

    /**
     * Resolve the slug, generating one from the name when not supplied.
     */
    private function resolveSlug(?string $slug, ?string $name): ?string
    {
        if ($slug !== null && $slug !== '') {
            return Str::slug($slug);
        }

        return $name !== null ? Str::slug($name) : null;
    }
}
