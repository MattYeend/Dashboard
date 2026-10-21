<?php

declare(strict_types=1);

namespace App\Services\Questions;

use App\Actions\Questions\ResolveQuestionableAction;
use App\Models\Question;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

class DataPreparationService
{
    public function __construct(
        private readonly ResolveQuestionableAction $resolveQuestionable,
    ) {}

    /**
     * Prepare question data for creation.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function prepareForCreation(array $data): array
    {
        $questionable = ($this->resolveQuestionable)(
            $data['questionable_type'] ?? null,
            $data['questionable_id'] ?? null,
        );

        if ($questionable === null) {
            throw new InvalidArgumentException('Unrecognised questionable.');
        }

        return [
            'questionable_type' => $questionable->getMorphClass(),
            'questionable_id' => $questionable->getKey(),
            'question' => $data['question'],
            'type' => $data['type'],
            'explanation' => $data['explanation'] ?? null,
            'points' => $data['points'] ?? 1,
            'position' => $data['position'] ?? $this->nextPosition($questionable),
            'is_required' => $data['is_required'] ?? true,
            'meta' => $data['meta'] ?? null,
        ];
    }

    /**
     * Prepare question data for update. The owner (questionable_*)
     * cannot be changed after creation.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function prepareForUpdate(array $data): array
    {
        $allowed = [
            'question',
            'type',
            'explanation',
            'points',
            'position',
            'is_required',
            'meta',
        ];

        $payload = [];

        foreach ($allowed as $field) {
            if (array_key_exists($field, $data)) {
                $payload[$field] = $data[$field];
            }
        }

        return $payload;
    }

    /**
     * Get the next free position for the given owner, including
     * soft-deleted questions so a restore never clashes.
     */
    private function nextPosition(Model $questionable): int
    {
        return (int) Question::withTrashed()
            ->where('questionable_type', $questionable->getMorphClass())
            ->where('questionable_id', $questionable->getKey())
            ->max('position') + 1;
    }
}
