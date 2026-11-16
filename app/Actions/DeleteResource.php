<?php

declare(strict_types=1);

namespace App\Actions;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class DeleteResource
{
    /**
     * Run pre-deletion side effects then soft delete the model.
     *
     * @template TModel of Model
     *
     * @param  TModel  $model
     * @param  callable(TModel): void  $beforeDelete
     */
    public function handle(Model $model, callable $beforeDelete): bool
    {
        return DB::transaction(function () use ($model, $beforeDelete): bool {
            $beforeDelete($model);

            return (bool) $model->delete();
        });
    }

    /**
     * Run pre-deletion side effects then permanently delete the model.
     *
     * @template TModel of Model
     *
     * @param  TModel  $model
     * @param  callable(TModel): void  $beforeDelete
     */
    public function forceHandle(Model $model, callable $beforeDelete): bool
    {
        return DB::transaction(function () use ($model, $beforeDelete): bool {
            $beforeDelete($model);

            return (bool) $model->forceDelete();
        });
    }
}
