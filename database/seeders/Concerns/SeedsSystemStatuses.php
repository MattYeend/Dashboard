<?php

declare(strict_types=1);

namespace Database\Seeders\Concerns;

use Illuminate\Database\Eloquent\Model;

trait SeedsSystemStatuses
{
    /**
     * Seed a set of statuses for one organisation, idempotently.
     *
     * Rows with a system_key are matched on (organisation_id, system_key) so a
     * renamed title never creates a duplicate. Rows without one are matched on
     * (organisation_id, title). The tenant global scope, and the soft delete
     * scope with it, is bypassed, so soft deleted rows are found and restored.
     *
     * Existing rows are not overwritten, which preserves edits made by users.
     *
     * @template TModel of Model
     *
     * @param  class-string<TModel>  $modelClass
     * @param  array<int, array<string, mixed>>  $statuses  Trusted, hard-coded seed data only.
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

            if ($systemKey !== null) {
                $query->where('system_key', $systemKey);
            } else {
                $query->whereNull('system_key')
                    ->where('title', $attributes['title']);
            }

            /** @var TModel|null $model */
            $model = $query->first();

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

            // Restore soft deleted rows without relying on the SoftDeletes trait,
            // which PHPStan cannot see on the generic Model type.
            if ($model->getAttribute('deleted_at') !== null) {
                $model->forceFill(['deleted_at' => null])->save();
            }
        }
    }
}
