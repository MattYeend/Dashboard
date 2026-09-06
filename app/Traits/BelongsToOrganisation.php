<?php

namespace App\Traits;

use App\Models\Organisation;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Scope;
use Spatie\Multitenancy\Models\Tenant;

/**
 * Scopes a model to its owning organisation and stamps organisation_id
 * automatically when a new record is created.
 *
 * @mixin Model
 * @method static void addGlobalScope(Scope|\Closure|string $scope, \Closure|null $implementation = null)
 * @method static void creating(\Closure|string $callback)
 */
trait BelongsToOrganisation
{
    /**
     * Boot the trait: add the organisation global scope and stamp the
     * current tenant's id onto new records.
     */
    protected static function bootBelongsToOrganisation(): void
    {
        static::addGlobalScope('organisation', function (Builder $query): void {
            if ($tenant = Tenant::current()) {
                $query->where(
                    $query->getModel()->getTable().'.organisation_id',
                    $tenant->id
                );
            }
        });

        static::creating(function (Model $model): void {
            if (empty($model->organisation_id) && ($tenant = Tenant::current())) {
                $model->organisation_id = $tenant->id;
            }
        });
    }

    /**
     * Get the organisation this record belongs to.
     *
     * @return BelongsTo<Organisation, Model>
     */
    public function organisation(): BelongsTo
    {
        /** @var Model $this */
        return $this->belongsTo(Organisation::class);
    }
}
