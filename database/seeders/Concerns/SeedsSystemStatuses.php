<?php

declare(strict_types=1);

namespace Database\Seeders\Concerns;

use Illuminate\Database\Eloquent\Model;

trait SeedsSystemStatuses
{
    /**
     * Seed statuses for an organisation, protecting system statuses.
     *
     * Safe to re-run:
     * - A status with a system_key is matched on that key, so a renamed
     *   title never produces a duplicate.
     * - A status without a key is matched on title and left untouched.
     * - Existing rows created before system_key existed are adopted by
     *   title and given their key.
     * - Soft deleted system rows are restored. Soft deleted custom rows
     *   are left alone, because they were deleted deliberately.
     * - Existing rows are never overwritten, so edited colours or
     *   descriptions survive a re-seed.
     *
     * Tenant and soft delete scopes are bypassed on purpose and the
     * organisation is filtered explicitly, so one tenant can never touch
     * another's rows.
     *
     * @param  class-string<Model>  $modelClass
     * @param  array<int, array<string, mixed>>  $statuses  Each needs a title; system statuses also need system_key.
     */
    protected function seedSystemStatuses(string $modelClass, int $organisationId, array $statuses): void
    {
        foreach ($statuses as $status) {
            $key = $status['system_key'] ?? null;

            $query = $modelClass::withoutGlobalScopes()
                ->where('organisation_id', $organisationId);

            $model = $key !== null
                ? ((clone $query)->where('system_key', $key)->first()
                    ?? (clone $query)->whereNull('system_key')->where('title', $status['title'])->first())
                : (clone $query)->where('title', $status['title'])->first();

            if ($model === null) {
                $model = new $modelClass;
                $model->forceFill([...$status, 'organisation_id' => $organisationId])->save();

                continue;
            }

            if ($key === null) {
                continue;
            }

            if ($model->system_key === null) {
                $model->forceFill(['system_key' => $key])->save();
            }

            if (method_exists($model, 'trashed') && $model->trashed()) {
                $model->restore();
            }
        }
    }
}
