<?php

declare(strict_types=1);

namespace Database\Seeders\Concerns;

use Illuminate\Database\Eloquent\Model;

trait SeedsSystemStatuses
{
    /**
     * Seed a set of statuses for one organisation, idempotently.
     *
     * Existing rows are matched by system key first, then title.
     * Soft deleted rows are included and restored when necessary.
     * Existing attributes are not overwritten.
     *
     * @template TModel of Model
     *
     * @param  class-string<TModel>  $modelClass
     * @param  array<int, array<string, mixed>>  $statuses
     */
    protected function seedSystemStatuses(
        string $modelClass,
        int $organisationId,
        array $statuses
    ): void {
        foreach ($statuses as $attributes) {
            $systemKey = $attributes['system_key'] ?? null;
            unset($attributes['system_key']);

            $query = (new $modelClass)
                ->newQuery()
                ->withoutGlobalScopes()
                ->where('organisation_id', $organisationId);

            $model = null;

            if ($systemKey !== null) {
                $model = (clone $query)
                    ->where('system_key', $systemKey)
                    ->first();
            }

            if ($model === null) {
                $model = (clone $query)
                    ->where('title', $attributes['title'])
                    ->first();
            }

            if ($model === null) {
                $model = new $modelClass;
                $model->forceFill($attributes);
                $model->forceFill([
                    'organisation_id' => $organisationId,
                    'system_key' => $systemKey,
                ]);
                $model->save();

                continue;
            }

            if (
                $systemKey !== null
                && $model->getAttribute('system_key') === null
            ) {
                $model->forceFill([
                    'system_key' => $systemKey,
                ]);
            }

            if ($model->getAttribute('deleted_at') !== null) {
                $model->forceFill(['deleted_at' => null]);
            }

            if ($model->isDirty()) {
                $model->save();
            }
        }
    }
}
