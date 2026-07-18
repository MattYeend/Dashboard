<?php

namespace App\Services\Posts;

use Illuminate\Http\UploadedFile;
use Mews\Purifier\Facades\Purifier;

class DataPreparationService
{
    /**
     * Prepare post data for creation.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function prepareForCreation(array $data, int $createdBy): array
    {
        return [
            'title' => $data['title'],
            'description' => Purifier::clean($data['description']),
            'image' => $this->storeImage($data['image'] ?? null),
            'meta' => $data['meta'] ?? null,
            'created_by' => $createdBy,
        ];
    }

    /**
     * Prepare post data for update.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function prepareForUpdate(array $data, int $updatedBy): array
    {
        $allowed = [
            'title',
            'description',
            'image',
            'meta',
        ];

        $payload = [];

        foreach ($allowed as $field) {
            if (! array_key_exists($field, $data)) {
                continue;
            }

            // Only touch the stored image when a new file was actually uploaded.
            // Otherwise the existing image would be nulled out on every update,
            // since the frontend always submits an `image` key.
            if ($field === 'image' && ! $data['image'] instanceof UploadedFile) {
                continue;
            }

            $payload[$field] = match ($field) {
                'description' => Purifier::clean($data[$field]),
                'image' => $this->storeImage($data[$field]),
                default => $data[$field],
            };
        }

        $payload['updated_by'] = $updatedBy;

        return $payload;
    }

    /**
     * Store an uploaded image and return its path, passing through
     * anything that isn't an uploaded file (e.g. an existing path, or null).
     */
    private function storeImage(mixed $image): ?string
    {
        if ($image instanceof UploadedFile) {
            return $image->store('posts', 'public');
        }

        return $image;
    }
}
