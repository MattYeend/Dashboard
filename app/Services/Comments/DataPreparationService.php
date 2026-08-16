<?php

namespace App\Services\Comments;

use InvalidArgumentException;
use Mews\Purifier\Facades\Purifier;

class DataPreparationService
{
    /**
     * Inject the required services into the data preparation service.
     */
    public function __construct(
        private readonly CommentableTypeRegistryService $registry,
    ) {}

    /**
     * Prepare comment data for creation.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function prepareForCreation(array $data): array
    {
        return [
            'commentable_type' => $this->resolveCommentableType($data['commentable_type']),
            'commentable_id' => $data['commentable_id'],
            'content' => Purifier::clean($data['content']),
            'meta' => $data['meta'] ?? null,
        ];
    }

    /**
     * Prepare comment data for update.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function prepareForUpdate(array $data): array
    {
        return [
            'content' => Purifier::clean($data['content']),
        ];
    }

    /**
     * Resolve the fully-qualified model class for a commentable type key.
     */
    private function resolveCommentableType(string $commentableType): string
    {
        return $this->registry->modelClassForKey($commentableType)
            ?? throw new InvalidArgumentException("Unrecognised commentable type: {$commentableType}");
    }
}
