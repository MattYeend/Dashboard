<?php

namespace App\Actions\Organisations;

use App\Models\Organisation;
use App\Models\User;

/**
 * Cascades a soft-delete, and later a hard-delete, from an Organisation to
 * every record scoped to it across all tenant-scoped models.
 */
class CascadeSoftDeleteOrganisationScopedRecords
{
    /**
     * Soft-delete every record scoped to the given Organisation, stamping
     * deleted_by on each record before removing it.
     */
    public function handle(Organisation $organisation, User $actor): void
    {
        foreach (config('organisations.scoped_models') as $modelClass) {
            $modelClass::query()
                ->withoutGlobalScope('organisation')
                ->where('organisation_id', $organisation->id)
                ->whereNull('deleted_at')
                ->get()
                ->each(function ($record) use ($actor): void {
                    $record->deleted_by = $actor->id;
                    $record->save();
                    $record->delete();
                });
        }
    }

    /**
     * Permanently hard-delete every record scoped to the given
     * Organisation, including records already soft-deleted.
     */
    public function hardHandle(Organisation $organisation): void
    {
        foreach (config('organisations.scoped_models') as $modelClass) {
            $modelClass::withTrashed()
                ->withoutGlobalScope('organisation')
                ->where('organisation_id', $organisation->id)
                ->forceDelete();
        }
    }
}
