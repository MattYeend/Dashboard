<?php

namespace App\Services\Comments;

use Mews\Purifier\Facades\Purifier;

class DataPreparationService
{
    /**
     * Prepare comment data for creation.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function prepareForCreation(
        array $data,
        int $postId,
        int $createdBy
    ): array {
        return [
            'post_id' => $postId,
            'content' => Purifier::clean($data['content']),
            'meta' => $data['meta'] ?? null,
            'created_by' => $createdBy,
        ];
    }
}
