<?php

declare(strict_types=1);

namespace App\Actions\Questions;

use Illuminate\Database\Eloquent\Model;

final class ResolveQuestionableAction
{
    /**
     * Resolve a morph alias and id to a registered model instance.
     */
    public function __invoke(mixed $alias, mixed $id): ?Model
    {
        if (! is_string($alias) || ! ctype_digit((string) $id)) {
            return null;
        }

        $class = config('questions.questionable_types', [])[$alias] ?? null;

        if (! is_string($class) || ! is_subclass_of($class, Model::class)) {
            return null;
        }

        return $class::query()->find((int) $id);
    }
}
