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
    public function prepareForCreation(array $data): array
    {
        return [
            'title' => $data['title'],
            'description' => Purifier::clean($data['description'], 'posts'),
            'image' => $this->storeImage($data['image'] ?? null),
            'meta' => $data['meta'] ?? null,
        ];
    }

    /**
     * Prepare post data for update.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function prepareForUpdate(array $data): array
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
                'description' => Purifier::clean($data['description'], 'posts'),
                'image' => $this->storeImage($data[$field]),
                default => $data[$field],
            };
        }

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
